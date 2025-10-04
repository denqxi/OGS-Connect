<?php

namespace Database\Seeders;

use App\Models\Tutor;
use Illuminate\Database\Seeder;

class TutorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if tutors already exist
        if (Tutor::count() > 0) {
            if ($this->command) {
                $this->command->info('Tutors already exist, skipping...');
            }
            return;
        }

        $tutors = array (
  0 => 
  array (
    'tutorID' => 'OGS-T0001',
    'tusername' => 'alicewong',
    'first_name' => 'Alice',
    'last_name' => 'Wong',
    'email' => 'alice.wong@example.com',
    'tpassword' => '$2y$12$0l2VTNozEAcL42VycF6S1eijAxzlD0EF7B4CcYzXGKOahrPTXOIuO',
    'phone_number' => '+1-555-0101',
    'sex' => 'F',
    'status' => 'active',
  ),
  1 => 
  array (
    'tutorID' => 'OGS-T0002',
    'tusername' => 'bobsmith',
    'first_name' => 'Bob',
    'last_name' => 'Smith',
    'email' => 'bob.smith@example.com',
    'tpassword' => '$2y$12$VHebvmw1J3vPzYVX.W.bTOck5zZdBNTTbitFkyu/Q.56ABhfVdsG.',
    'phone_number' => '+1-555-0102',
    'sex' => 'M',
    'status' => 'active',
  ),
  2 => 
  array (
    'tutorID' => 'OGS-T0003',
    'tusername' => 'caroljohnson',
    'first_name' => 'Carol',
    'last_name' => 'Johnson',
    'email' => 'carol.johnson@example.com',
    'tpassword' => '$2y$12$4.9YRITJwikMRm8zh2AB7e8IZqusK1bLg8aW2FqOO7u.wF3kjTYze',
    'phone_number' => '+1-555-0103',
    'sex' => 'F',
    'status' => 'active',
  ),
  3 => 
  array (
    'tutorID' => 'OGS-T0004',
    'tusername' => 'davidlee',
    'first_name' => 'David',
    'last_name' => 'Lee',
    'email' => 'david.lee@example.com',
    'tpassword' => '$2y$12$1JBXpMucmG3.wYvKyBVwiuASg91XKbWRcGsjLL4oMppsloue2q38m',
    'phone_number' => '+1-555-0104',
    'sex' => 'M',
    'status' => 'active',
  ),
  4 => 
  array (
    'tutorID' => 'OGS-T0005',
    'tusername' => 'emilychen',
    'first_name' => 'Emily',
    'last_name' => 'Chen',
    'email' => 'emily.chen@example.com',
    'tpassword' => '$2y$12$ogdXii2.trHb9E5P3XkqqOLvh3DtsHJ27XXm6CCVsEkPjGkxpIHQ2',
    'phone_number' => '+1-555-0105',
    'sex' => 'F',
    'status' => 'active',
  ),
  5 => 
  array (
    'tutorID' => 'OGS-T0006',
    'tusername' => 'frankbrown',
    'first_name' => 'Frank',
    'last_name' => 'Brown',
    'email' => 'frank.brown@example.com',
    'tpassword' => '$2y$12$HJ7Sb5tmR24sW80eMOqkTOuahx5pqdxOYqsEtq4s7qhxe1qxfQGFa',
    'phone_number' => '+1-555-0106',
    'sex' => 'M',
    'status' => 'inactive',
  ),
  6 => 
  array (
    'tutorID' => 'OGS-T0007',
    'tusername' => 'gracewilson',
    'first_name' => 'Grace',
    'last_name' => 'Wilson',
    'email' => 'grace.wilson@example.com',
    'tpassword' => '$2y$12$zKi9mG3uahvrtWmVNdU/UOkavO5lCrIcpwp3amZxH4XzVqIGZvopW',
    'phone_number' => '+1-555-0107',
    'sex' => 'F',
    'status' => 'active',
  ),
  7 => 
  array (
    'tutorID' => 'OGS-T0008',
    'tusername' => 'henrydavis',
    'first_name' => 'Henry',
    'last_name' => 'Davis',
    'email' => 'henry.davis@example.com',
    'tpassword' => '$2y$12$4f3oCzEVyEZD14WHicjCDO9jXnChSfwMh7kDM7YEFqvIPPFsm1rru',
    'phone_number' => '+1-555-0108',
    'sex' => 'M',
    'status' => 'active',
  ),
  8 => 
  array (
    'tutorID' => 'OGS-T0009',
    'tusername' => 'irisgarcia',
    'first_name' => 'Iris',
    'last_name' => 'Garcia',
    'email' => 'iris.garcia@example.com',
    'tpassword' => '$2y$12$9WnHoadGw2s3jrp0yoW2ze53H0XjKpqIlAAFWsDbP7FYYrtHJ9jYe',
    'phone_number' => '+1-555-0109',
    'sex' => 'F',
    'status' => 'active',
  ),
  9 => 
  array (
    'tutorID' => 'OGS-T0010',
    'tusername' => 'jackmartinez',
    'first_name' => 'Jack',
    'last_name' => 'Martinez',
    'email' => 'jack.martinez@example.com',
    'tpassword' => '$2y$12$960ckmw88o89kNoeXwKpM.t1es3vGhLTqF.w7uDjyZiFWFrO8l55m',
    'phone_number' => '+1-555-0110',
    'sex' => 'M',
    'status' => 'active',
  ),
  10 => 
  array (
    'tutorID' => 'OGS-T0011',
    'tusername' => 'karenanderson',
    'first_name' => 'Karen',
    'last_name' => 'Anderson',
    'email' => 'karen.anderson@example.com',
    'tpassword' => '$2y$12$fm6WsEhkUjRJDkXn4SOm1.h91BjfQRMK8bh0N0AQjm.J7AIvyAkMy',
    'phone_number' => '+1-555-0111',
    'sex' => 'F',
    'status' => 'active',
  ),
  11 => 
  array (
    'tutorID' => 'OGS-T0012',
    'tusername' => 'lukethompson',
    'first_name' => 'Luke',
    'last_name' => 'Thompson',
    'email' => 'luke.thompson@example.com',
    'tpassword' => '$2y$12$o1EaBi560qhOXQXI4MHd3OrOH1G7WxOBwLb1x7B.jQd7URf5o392W',
    'phone_number' => '+1-555-0112',
    'sex' => 'M',
    'status' => 'active',
  ),
  12 => 
  array (
    'tutorID' => 'OGS-T0013',
    'tusername' => 'mariarodriguez',
    'first_name' => 'Maria',
    'last_name' => 'Rodriguez',
    'email' => 'maria.rodriguez@example.com',
    'tpassword' => '$2y$12$afnzBCX0utLgF37/9GfrYO94zug2TXnRyIgyh.MrS4Uh.XbK1F7nq',
    'phone_number' => '+1-555-0113',
    'sex' => 'F',
    'status' => 'active',
  ),
  13 => 
  array (
    'tutorID' => 'OGS-T0014',
    'tusername' => 'nickwhite',
    'first_name' => 'Nick',
    'last_name' => 'White',
    'email' => 'nick.white@example.com',
    'tpassword' => '$2y$12$PxcuxtqwObAxDqywlwRCcOGTCVgbfcw45YfWklhDOt.wnCwG3Uiz2',
    'phone_number' => '+1-555-0114',
    'sex' => 'M',
    'status' => 'active',
  ),
  14 => 
  array (
    'tutorID' => 'OGS-T0015',
    'tusername' => 'oliviataylor',
    'first_name' => 'Olivia',
    'last_name' => 'Taylor',
    'email' => 'olivia.taylor@example.com',
    'tpassword' => '$2y$12$I38eLnsIQbzRhK1s0tkhqeTppQBG1YOWAF27u.CQ2AjMhn58kmLC2',
    'phone_number' => '+1-555-0115',
    'sex' => 'F',
    'status' => 'active',
  ),
  15 => 
  array (
    'tutorID' => 'OGS-T0016',
    'tusername' => 'peterclark',
    'first_name' => 'Peter',
    'last_name' => 'Clark',
    'email' => 'peter.clark@example.com',
    'tpassword' => '$2y$12$NwJS.aEccIKsO0e9qh1jqO206OOz/qK4CjGM53r7w6nzzIMmD8vTu',
    'phone_number' => '+1-555-0116',
    'sex' => 'M',
    'status' => 'active',
  ),
  16 => 
  array (
    'tutorID' => 'OGS-T0017',
    'tusername' => 'quinnlewis',
    'first_name' => 'Quinn',
    'last_name' => 'Lewis',
    'email' => 'quinn.lewis@example.com',
    'tpassword' => '$2y$12$DFaVirwRDNY6dAZnioN.oe.0IRZXYo2GJkTGVlPeLwKrz2gqlOORS',
    'phone_number' => '+1-555-0117',
    'sex' => 'F',
    'status' => 'active',
  ),
  17 => 
  array (
    'tutorID' => 'OGS-T0018',
    'tusername' => 'ryanwalker',
    'first_name' => 'Ryan',
    'last_name' => 'Walker',
    'email' => 'ryan.walker@example.com',
    'tpassword' => '$2y$12$h/tfg3kHpw6e2.ogIA9B8.kxhpnSF3yYdpUbux5YykyWoH5M6b.tC',
    'phone_number' => '+1-555-0118',
    'sex' => 'M',
    'status' => 'active',
  ),
  18 => 
  array (
    'tutorID' => 'OGS-T0019',
    'tusername' => 'sophiahall',
    'first_name' => 'Sophia',
    'last_name' => 'Hall',
    'email' => 'sophia.hall@example.com',
    'tpassword' => '$2y$12$BeDvu515Ltt3dr5pb0Qj5OaaXq5D5JOlVD/I4oIHEk9yuyQCepTk.',
    'phone_number' => '+1-555-0119',
    'sex' => 'F',
    'status' => 'active',
  ),
  19 => 
  array (
    'tutorID' => 'OGS-T0020',
    'tusername' => 'tylerallen',
    'first_name' => 'Tyler',
    'last_name' => 'Allen',
    'email' => 'tyler.allen@example.com',
    'tpassword' => '$2y$12$IApI8ku4w7TZT.Ub8WcHBOmBx0syWd.0oDhqb0njoWHOynNxBydrS',
    'phone_number' => '+1-555-0120',
    'sex' => 'M',
    'status' => 'active',
  ),
  20 => 
  array (
    'tutorID' => 'OGS-T0021',
    'tusername' => 'unajones',
    'first_name' => 'Una',
    'last_name' => 'Jones',
    'email' => 'una.jones@example.com',
    'tpassword' => '$2y$12$RMjP0iAp6VG2DSSPNxlQ7ez8fas0puZr6d2RztsXzH8KMQ.coBN5G',
    'phone_number' => '+1-555-0121',
    'sex' => 'F',
    'status' => 'active',
  ),
  21 => 
  array (
    'tutorID' => 'OGS-T0022',
    'tusername' => 'victorwright',
    'first_name' => 'Victor',
    'last_name' => 'Wright',
    'email' => 'victor.wright@example.com',
    'tpassword' => '$2y$12$HKt9CjzMwOWKppgRhTSMIuICEEE8ICXKcmLzkYJqP3UVPwJCxoLce',
    'phone_number' => '+1-555-0122',
    'sex' => 'M',
    'status' => 'active',
  ),
  22 => 
  array (
    'tutorID' => 'OGS-T0023',
    'tusername' => 'wendylopez',
    'first_name' => 'Wendy',
    'last_name' => 'Lopez',
    'email' => 'wendy.lopez@example.com',
    'tpassword' => '$2y$12$dU3HKlJc.c/O/uSAFrg6reyi4c3cZqBLx6Dv4lurG6DKzN2Mv/KSG',
    'phone_number' => '+1-555-0123',
    'sex' => 'F',
    'status' => 'active',
  ),
  23 => 
  array (
    'tutorID' => 'OGS-T0024',
    'tusername' => 'xavierhill',
    'first_name' => 'Xavier',
    'last_name' => 'Hill',
    'email' => 'xavier.hill@example.com',
    'tpassword' => '$2y$12$hf4/WIwuUZ4V/Bq1rnkpeuU21HyByYBe7VAMIw15OUj8OqqRUgvdm',
    'phone_number' => '+1-555-0124',
    'sex' => 'M',
    'status' => 'active',
  ),
  24 => 
  array (
    'tutorID' => 'OGS-T0025',
    'tusername' => 'chrisdavis25',
    'first_name' => 'Chris',
    'last_name' => 'Davis',
    'email' => 'chris.davis25@example.com',
    'tpassword' => '$2y$12$27ArSbGGaiInUnMIgeadquk/2bXOqFHK7/aV.BaogSBWN/H9t8IP2',
    'phone_number' => '+1-555-0125',
    'sex' => 'F',
    'status' => 'active',
  ),
  25 => 
  array (
    'tutorID' => 'OGS-T0026',
    'tusername' => 'katehall26',
    'first_name' => 'Kate',
    'last_name' => 'Hall',
    'email' => 'kate.hall26@example.com',
    'tpassword' => '$2y$12$qD5f0rZFQPh5QRRXjsOrPeGFqkaRYCO8R0dK7x3D6thX9LB.i43wy',
    'phone_number' => '+1-555-0126',
    'sex' => 'M',
    'status' => 'active',
  ),
  26 => 
  array (
    'tutorID' => 'OGS-T0027',
    'tusername' => 'tomhall27',
    'first_name' => 'Tom',
    'last_name' => 'Hall',
    'email' => 'tom.hall27@example.com',
    'tpassword' => '$2y$12$Oi0KwV8uKf6Hn4ysBAMuCeAAm2M41uQRtii2mTmgj.RM7FecYaxbK',
    'phone_number' => '+1-555-0127',
    'sex' => 'M',
    'status' => 'active',
  ),
  27 => 
  array (
    'tutorID' => 'OGS-T0028',
    'tusername' => 'alexmartin28',
    'first_name' => 'Alex',
    'last_name' => 'Martin',
    'email' => 'alex.martin28@example.com',
    'tpassword' => '$2y$12$6rJCKzxhEr8keBLakkYfNe1p9JankSZr/ZGcl4JR7GztNQyETNzSC',
    'phone_number' => '+1-555-0128',
    'sex' => 'F',
    'status' => 'active',
  ),
  28 => 
  array (
    'tutorID' => 'OGS-T0029',
    'tusername' => 'tomthompson29',
    'first_name' => 'Tom',
    'last_name' => 'Thompson',
    'email' => 'tom.thompson29@example.com',
    'tpassword' => '$2y$12$/NSqfyh8UEme0icw46yjHudtvA5zM/V6qIMLpMZ9CjLqw22jr3yQa',
    'phone_number' => '+1-555-0129',
    'sex' => 'M',
    'status' => 'active',
  ),
  29 => 
  array (
    'tutorID' => 'OGS-T0030',
    'tusername' => 'alexallen30',
    'first_name' => 'Alex',
    'last_name' => 'Allen',
    'email' => 'alex.allen30@example.com',
    'tpassword' => '$2y$12$5wINeYkl8ezCfArAgL3Qoutlg8XKfWEHNGLOGVIMPir1oq2CGQ8G6',
    'phone_number' => '+1-555-0130',
    'sex' => 'F',
    'status' => 'active',
  ),
  30 => 
  array (
    'tutorID' => 'OGS-T0031',
    'tusername' => 'mariamartin31',
    'first_name' => 'Maria',
    'last_name' => 'Martin',
    'email' => 'maria.martin31@example.com',
    'tpassword' => '$2y$12$HVgGDlcp8X7S1lDKrY/6OeC9MApSsjhme3Dj1ycb7DS8Aj55G2/DO',
    'phone_number' => '+1-555-0131',
    'sex' => 'M',
    'status' => 'active',
  ),
  31 => 
  array (
    'tutorID' => 'OGS-T0032',
    'tusername' => 'emmawhite32',
    'first_name' => 'Emma',
    'last_name' => 'White',
    'email' => 'emma.white32@example.com',
    'tpassword' => '$2y$12$clxwOtmRG0/Z7Fel0QKWR.fH3RNQbS/zoEBjga4aLXtAstUx8VNtK',
    'phone_number' => '+1-555-0132',
    'sex' => 'F',
    'status' => 'active',
  ),
  32 => 
  array (
    'tutorID' => 'OGS-T0033',
    'tusername' => 'alexthompson33',
    'first_name' => 'Alex',
    'last_name' => 'Thompson',
    'email' => 'alex.thompson33@example.com',
    'tpassword' => '$2y$12$ySu8Ev130s2uVnIFy1/yRO/4HbUWBseLNu3OvL1sl8fdtiNVju5ru',
    'phone_number' => '+1-555-0133',
    'sex' => 'F',
    'status' => 'active',
  ),
  33 => 
  array (
    'tutorID' => 'OGS-T0034',
    'tusername' => 'katewalker34',
    'first_name' => 'Kate',
    'last_name' => 'Walker',
    'email' => 'kate.walker34@example.com',
    'tpassword' => '$2y$12$bSIzrSIBfMCA5EZ8ej3AYuH9dFNSugOzP72KEBcu9qNTB2rqMsToy',
    'phone_number' => '+1-555-0134',
    'sex' => 'M',
    'status' => 'active',
  ),
  34 => 
  array (
    'tutorID' => 'OGS-T0035',
    'tusername' => 'meganlopez35',
    'first_name' => 'Megan',
    'last_name' => 'Lopez',
    'email' => 'megan.lopez35@example.com',
    'tpassword' => '$2y$12$WZI3BgozGVQrhTSKEowaE.jq7HXtWApcA8ViK4JzgeXarVRQ3wICe',
    'phone_number' => '+1-555-0135',
    'sex' => 'M',
    'status' => 'active',
  ),
  35 => 
  array (
    'tutorID' => 'OGS-T0036',
    'tusername' => 'mariayoung36',
    'first_name' => 'Maria',
    'last_name' => 'Young',
    'email' => 'maria.young36@example.com',
    'tpassword' => '$2y$12$kwSEzCaz63FA1/JmcY0xZuCIRpdDtWl8g3HM.bCvVip.tPK5yIcbe',
    'phone_number' => '+1-555-0136',
    'sex' => 'F',
    'status' => 'active',
  ),
  36 => 
  array (
    'tutorID' => 'OGS-T0037',
    'tusername' => 'samwilson37',
    'first_name' => 'Sam',
    'last_name' => 'Wilson',
    'email' => 'sam.wilson37@example.com',
    'tpassword' => '$2y$12$lPFe5v3KcQt4RWO3wEKW5.VjeYDxcePEJ70njQPDABkDb/qrFHLq6',
    'phone_number' => '+1-555-0137',
    'sex' => 'M',
    'status' => 'active',
  ),
  37 => 
  array (
    'tutorID' => 'OGS-T0038',
    'tusername' => 'ryanthomas38',
    'first_name' => 'Ryan',
    'last_name' => 'Thomas',
    'email' => 'ryan.thomas38@example.com',
    'tpassword' => '$2y$12$rD.rvPlzBDYZ3vK6rKFtRuUJ6voZdpvyTkwJanrh2zBMiTZnmclhG',
    'phone_number' => '+1-555-0138',
    'sex' => 'M',
    'status' => 'active',
  ),
  38 => 
  array (
    'tutorID' => 'OGS-T0039',
    'tusername' => 'benclark39',
    'first_name' => 'Ben',
    'last_name' => 'Clark',
    'email' => 'ben.clark39@example.com',
    'tpassword' => '$2y$12$2oEiXidWwHoLrvaaPgHJ3.UTE33LVobU7lA9lkOtjHqKCuA3NaTq6',
    'phone_number' => '+1-555-0139',
    'sex' => 'F',
    'status' => 'active',
  ),
  39 => 
  array (
    'tutorID' => 'OGS-T0040',
    'tusername' => 'nicolemartin40',
    'first_name' => 'Nicole',
    'last_name' => 'Martin',
    'email' => 'nicole.martin40@example.com',
    'tpassword' => '$2y$12$8FGimFgK4JfGpn5GDxqdfenJl7iuIVYmjQ3ZZoU5wfBF6yusqNk72',
    'phone_number' => '+1-555-0140',
    'sex' => 'M',
    'status' => 'active',
  ),
  40 => 
  array (
    'tutorID' => 'OGS-T0041',
    'tusername' => 'rachelclark41',
    'first_name' => 'Rachel',
    'last_name' => 'Clark',
    'email' => 'rachel.clark41@example.com',
    'tpassword' => '$2y$12$qJO67S13Imt1RXtzCjPk9.kMtMF5V9i./UtBOHV56BIuPYxY1YinC',
    'phone_number' => '+1-555-0141',
    'sex' => 'F',
    'status' => 'active',
  ),
  41 => 
  array (
    'tutorID' => 'OGS-T0042',
    'tusername' => 'chrislopez42',
    'first_name' => 'Chris',
    'last_name' => 'Lopez',
    'email' => 'chris.lopez42@example.com',
    'tpassword' => '$2y$12$IKbk5tOTb5RXnu.LlnW4autxRVOWaf/thzWLLsmdDZKioU8DAhd7q',
    'phone_number' => '+1-555-0142',
    'sex' => 'F',
    'status' => 'active',
  ),
  42 => 
  array (
    'tutorID' => 'OGS-T0043',
    'tusername' => 'jamesthomas43',
    'first_name' => 'James',
    'last_name' => 'Thomas',
    'email' => 'james.thomas43@example.com',
    'tpassword' => '$2y$12$i1At7bd13RkMG8VjpEPZYu4Zv0o2P/q82h4eN0NL4YFFfZZ8ZO7pS',
    'phone_number' => '+1-555-0143',
    'sex' => 'F',
    'status' => 'active',
  ),
  43 => 
  array (
    'tutorID' => 'OGS-T0044',
    'tusername' => 'annalopez44',
    'first_name' => 'Anna',
    'last_name' => 'Lopez',
    'email' => 'anna.lopez44@example.com',
    'tpassword' => '$2y$12$ecaLz.PEaRyfQemURr8M/OVUzAOpT8CYpVMsQ2g3XHo3xQoGPwz7W',
    'phone_number' => '+1-555-0144',
    'sex' => 'M',
    'status' => 'active',
  ),
  44 => 
  array (
    'tutorID' => 'OGS-T0045',
    'tusername' => 'tomking45',
    'first_name' => 'Tom',
    'last_name' => 'King',
    'email' => 'tom.king45@example.com',
    'tpassword' => '$2y$12$rWcfoV8GEKb9tanIBHfCuO0mLdx5xUfHOe3knMRW8k2kZvir8OGGC',
    'phone_number' => '+1-555-0145',
    'sex' => 'F',
    'status' => 'active',
  ),
  45 => 
  array (
    'tutorID' => 'OGS-T0046',
    'tusername' => 'amyanderson46',
    'first_name' => 'Amy',
    'last_name' => 'Anderson',
    'email' => 'amy.anderson46@example.com',
    'tpassword' => '$2y$12$HZWMdFsJ2CVHzlvj983DVOFD7L.9szPgMslvZ7W1LTCaUmnejHUWO',
    'phone_number' => '+1-555-0146',
    'sex' => 'F',
    'status' => 'active',
  ),
  46 => 
  array (
    'tutorID' => 'OGS-T0047',
    'tusername' => 'paulclark47',
    'first_name' => 'Paul',
    'last_name' => 'Clark',
    'email' => 'paul.clark47@example.com',
    'tpassword' => '$2y$12$2sv1PdTKaJH.WP87HLElqumBdwS6wLeAqqUaUkbf8p8sYCRm57aOq',
    'phone_number' => '+1-555-0147',
    'sex' => 'M',
    'status' => 'active',
  ),
  47 => 
  array (
    'tutorID' => 'OGS-T0048',
    'tusername' => 'sophiemartinez48',
    'first_name' => 'Sophie',
    'last_name' => 'Martinez',
    'email' => 'sophie.martinez48@example.com',
    'tpassword' => '$2y$12$Sn6YUfSTuSsjD.ZLRk.fFe6AoVEMbBVxxxcU6.0kAbadkDXkwcw3O',
    'phone_number' => '+1-555-0148',
    'sex' => 'F',
    'status' => 'active',
  ),
  48 => 
  array (
    'tutorID' => 'OGS-T0049',
    'tusername' => 'rachelwalker49',
    'first_name' => 'Rachel',
    'last_name' => 'Walker',
    'email' => 'rachel.walker49@example.com',
    'tpassword' => '$2y$12$XXWPGBBGMb2bDWkCJzqrU.6G5UO8lREAvnIbntDmKbhnHAQxv1.5e',
    'phone_number' => '+1-555-0149',
    'sex' => 'M',
    'status' => 'active',
  ),
  49 => 
  array (
    'tutorID' => 'OGS-T0050',
    'tusername' => 'marialopez50',
    'first_name' => 'Maria',
    'last_name' => 'Lopez',
    'email' => 'maria.lopez50@example.com',
    'tpassword' => '$2y$12$ZfhorSYkxarixFHObxy0NOHX8WqzVLTO0Q3AJt/rZeqt02T4N4hwO',
    'phone_number' => '+1-555-0150',
    'sex' => 'M',
    'status' => 'active',
  ),
);

        foreach ($tutors as $tutorData) {
            Tutor::create($tutorData);
        }

        if ($this->command) {
            $this->command->info('✅ Created ' . count($tutors) . ' tutor accounts for testing');
        }
    }
}