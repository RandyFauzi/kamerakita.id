$ErrorActionPreference = 'Stop'
$connectorToken = [Environment]::GetEnvironmentVariable('ROBOTICS_1618_BOT_TOKEN', 'User')
if (-not $connectorToken) { throw 'ROBOTICS_1618_BOT_TOKEN belum diset untuk user Windows ini.' }
$connectorEndpoint = [Environment]::GetEnvironmentVariable('ROBOTICS_1618_SYNC_URL', 'User')
if (-not $connectorEndpoint) { $connectorEndpoint = 'https://kamerakitaid.site/api/bot/1618/sync' }
if (-not $connectorEndpoint.StartsWith('https://')) { throw 'Endpoint wajib HTTPS.' }
$ready = $false
try {
    Invoke-WebRequest -UseBasicParsing -Uri $connectorEndpoint -Method Post -ContentType 'application/json' -Headers @{'X-Bot-Token' = $connectorToken; Accept = 'application/json'} -Body '{}' | Out-Null
} catch {
    if ($_.Exception.Response -and [int]$_.Exception.Response.StatusCode -eq 422) { $ready = $true }
    else { throw 'Backend 1618 belum siap: deploy, set token server, dan jalankan migrasi dahulu. Jadwal belum diubah.' }
}
if (-not $ready) { throw 'Respons preflight tidak sesuai; jadwal belum diubah.' }
$connectorPath = Join-Path $PSScriptRoot 'sync_1618.bat'
$quotedPath = $connectorPath.Replace("'", "''")
$taskCommand = "& '" + $quotedPath + "' --scheduled" + [Environment]::NewLine + 'exit $LASTEXITCODE'
$encodedCommand = [Convert]::ToBase64String([Text.Encoding]::Unicode.GetBytes($taskCommand))
$action = New-ScheduledTaskAction -Execute 'powershell.exe' -Argument "-NoProfile -WindowStyle Hidden -EncodedCommand $encodedCommand" -WorkingDirectory $PSScriptRoot
$triggers = @((New-ScheduledTaskTrigger -Daily -At '10:15'), (New-ScheduledTaskTrigger -Daily -At '19:15'))
$principal = New-ScheduledTaskPrincipal -UserId ([System.Security.Principal.WindowsIdentity]::GetCurrent().Name) -LogonType Interactive -RunLevel Limited
$settings = New-ScheduledTaskSettingsSet -StartWhenAvailable -MultipleInstances IgnoreNew -ExecutionTimeLimit (New-TimeSpan -Hours 1)
Register-ScheduledTask -TaskName 'KameraKita_1618_Sync' -Action $action -Trigger $triggers -Principal $principal -Settings $settings -Force | Select-Object TaskName, State
Write-Output 'Jadwal 1618 aktif 10:15 dan 19:15 waktu Windows. Jadwal Atlas tidak diubah.'
