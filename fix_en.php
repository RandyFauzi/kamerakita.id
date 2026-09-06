<?php

$en = require 'lang/en/landing.php';

$en['features']['title2'] = 'Simple work, maximum results!';
$en['features']['subtitle2'] = 'Just record your daily activities from home. Approved reports transparently enter your earnings summary.';
$en['features']['item_1_title'] = 'Help AI Get Smarter';
$en['features']['item_1_desc'] = 'KameraKita AI teaches smart technology (AI) to understand human activities inside the house.';
$en['features']['item_2_title'] = 'Record Home Activities';
$en['features']['item_2_desc'] = 'Your task is just wearing a head mount and recording daily activities like mopping, washing dishes, or cleaning up.';
$en['features']['item_3_title'] = 'Send Video & Get Paid';
$en['features']['item_3_desc'] = 'Housework done, wallet stays thick. No special skills needed, everyone can definitely do it!';

$en['calculator']['title2'] = 'Choose the work rhythm that fits you';
$en['calculator']['subtitle2'] = 'Base rate is Rp60,000 per hour of clean recording. This simulation helps you imagine your weekly potential before starting.';

$en['steps']['title2'] = 'Only 3 steps to start earning';
$en['steps']['subtitle2'] = 'From registration to payment, everything is made simple and guided by the KameraKita team.';
$en['steps']['step_1_title'] = 'Join & Attend Briefing';
$en['steps']['step_1_desc'] = 'Register via WhatsApp, and our team will explain how it works, tasks, and equipment needs.';
$en['steps']['step_2_title'] = 'Record Your Activities';
$en['steps']['step_2_desc'] = 'Choose available tasks, mount your phone according to guidelines, and record daily activities as usual.';
$en['steps']['step_3_title'] = 'Upload & Get Paid';
$en['steps']['step_3_desc'] = 'Send your recordings for checking. Once they pass QC, approved durations are added to your monthly payout.';

$en['testimonials']['title2'] = 'Stories of contributors who started earning extra income';
$en['testimonials']['subtitle2'] = 'Experiences that describe how the workflow, QC, and payments are executed neatly.';

$en['faq']['title'] = 'Frequently Asked Questions';
$en['faq']['q1_q'] = 'Is there a registration fee for contributors?';
$en['faq']['q1_a'] = '100% FREE. We never charge any fees to contributors. Any form of collection on behalf of KameraKita is a scam.';
$en['faq']['q3_q2'] = 'When will the accumulated duration commissions be disbursed?';
$en['faq']['q3_a2'] = 'Commission disbursements are processed manually by admins according to the operational schedule based on approved durations.';

$out = "<?php\n\nreturn " . var_export($en, true) . ";\n";
file_put_contents('lang/en/landing.php', $out);
