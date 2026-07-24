<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $emails = [
            'mitajnb@web-library.net',
            'yuliarahmah@web-library.net',
            'restram01@web-library.net',
            'riduan@web-library.net',
            'pdftjomiadi1@web-library.net',
            'frigia01@gmail.com',
            'isnazay11r@gmail.com',
            'faizalrif19@gmail.com',
            'lionelyoga600@gmail.com',
            'bt.akun16@web-library.net',
            'btakun17@web-library.net',
            'pta@web-library.net',
            'triutari@web-library.net',
            'shinta1995@web-library.net',
            'auliacantik26@web-library.net',
            'zahraszy@web-library.net',
            'stormie33@web-library.net',
            'yulianaeya760@gmail.com',
            'hikmah23hik@web-library.net',
            'ninnaaulia@web-library.net',
            'bagussfajrian@web-library.net',
            'lindaatlas2026@gmail.com',
            'rawalinda26@gmail.com',
            'fariddani997@gmail.com',
            'rizkahumaira455@gmail.com',
            'yunjinokey@web-library.net',
            'mamaaqiela@web-library.net',
            'bilaocean@web-library.net',
            'mellyhafiz28@web-library.net',
            'aldi@web-library.net',
            'ptma15@web-library.net',
            'dhifa@web-library.net',
            'biy@web-library.net',
            'ira@web-library.net',
            'amad06@web-library.net',
            'ndys20@web-library.net',
            'nurinuri@web-library.net',
            'gstptri@web-library.net',
            'chang003@web-library.net',
            'ayu@web-library.net',
            'mayaputri67@web-library.net',
            'nannosugar0309@web-library.net',
            'lukiman@web-library.net',
            'indiraer@web-library.net',
        ];

        DB::table('partners')->whereIn('email', $emails)->update(['is_client_registered' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No strict reverse action needed or can set to false
    }
};
