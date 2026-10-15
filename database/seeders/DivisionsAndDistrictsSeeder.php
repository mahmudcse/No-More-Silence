<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\District;
use App\Models\Division;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DivisionsAndDistrictsSeeder extends Seeder
{
    public function run(): void
    {
        District::query()->delete();
        Division::query()->delete();

        $divisions = [
            ['code' => '10', 'name_en' => 'Barishal', 'name_bn' => 'বরিশাল'],
            ['code' => '20', 'name_en' => 'Chattogram', 'name_bn' => 'চট্টগ্রাম'],
            ['code' => '30', 'name_en' => 'Dhaka', 'name_bn' => 'ঢাকা'],
            ['code' => '40', 'name_en' => 'Khulna', 'name_bn' => 'খুলনা'],
            ['code' => '45', 'name_en' => 'Mymensingh', 'name_bn' => 'ময়মনসিংহ'],
            ['code' => '50', 'name_en' => 'Rajshahi', 'name_bn' => 'রাজশাহী'],
            ['code' => '55', 'name_en' => 'Rangpur', 'name_bn' => 'রংপুর'],
            ['code' => '70', 'name_en' => 'Sylhet', 'name_bn' => 'সিলেট'],
        ];

        $districts = [
            // Barishal Division (10)
            ['division' => '10', 'code' => '4', 'name_en' => 'Barguna', 'name_bn' => 'বরগুনা', 'population' => 1010540],
            ['division' => '10', 'code' => '42', 'name_en' => 'Jhalakathi', 'name_bn' => 'ঝালকাঠি', 'population' => 661160],
            ['division' => '10', 'code' => '6', 'name_en' => 'Barishal', 'name_bn' => 'বরিশাল', 'population' => 2570450],
            ['division' => '10', 'code' => '78', 'name_en' => 'Patuakhali', 'name_bn' => 'পটুয়াখালী', 'population' => 1727254],
            ['division' => '10', 'code' => '79', 'name_en' => 'Pirojpur', 'name_bn' => 'পিরোজপুর', 'population' => 1198193],
            ['division' => '10', 'code' => '9', 'name_en' => 'Bhola', 'name_bn' => 'ভোলা', 'population' => 1932514],

            // Chattogram Division (20)
            ['division' => '20', 'code' => '12', 'name_en' => 'Brahmanbaria', 'name_bn' => 'ব্রাহ্মণবাড়িয়া', 'population' => 3306559],
            ['division' => '20', 'code' => '13', 'name_en' => 'Chandpur', 'name_bn' => 'চাঁদপুর', 'population' => 2635748],
            ['division' => '20', 'code' => '15', 'name_en' => 'Chattogram', 'name_bn' => 'চট্টগ্রাম', 'population' => 9169464],
            ['division' => '20', 'code' => '19', 'name_en' => 'Cumilla', 'name_bn' => 'কুমিল্লা', 'population' => 6212216],
            ['division' => '20', 'code' => '22', 'name_en' => 'Coxsbazar', 'name_bn' => 'কক্সবাজার', 'population' => 2824115],
            ['division' => '20', 'code' => '3', 'name_en' => 'Bandarban', 'name_bn' => 'বান্দরবান', 'population' => 481109],
            ['division' => '20', 'code' => '30', 'name_en' => 'Feni', 'name_bn' => 'ফেনী', 'population' => 1648896],
            ['division' => '20', 'code' => '46', 'name_en' => 'Khagrachari', 'name_bn' => 'খাগড়াছড়ি', 'population' => 714119],
            ['division' => '20', 'code' => '51', 'name_en' => 'Lakshmipur', 'name_bn' => 'লক্ষ্মীপুর', 'population' => 1938111],
            ['division' => '20', 'code' => '75', 'name_en' => 'Noakhali', 'name_bn' => 'নোয়াখালী', 'population' => 3625252],
            ['division' => '20', 'code' => '84', 'name_en' => 'Rangamati', 'name_bn' => 'রাঙ্গামাটি পার্বত্য', 'population' => 647587],

            // Dhaka Division (30)
            ['division' => '30', 'code' => '26', 'name_en' => 'Dhaka', 'name_bn' => 'ঢাকা', 'population' => 14734025],
            ['division' => '30', 'code' => '29', 'name_en' => 'Faridpur', 'name_bn' => 'ফরিদপুর', 'population' => 2162876],
            ['division' => '30', 'code' => '33', 'name_en' => 'Gazipur', 'name_bn' => 'গাজীপুর', 'population' => 5263474],
            ['division' => '30', 'code' => '35', 'name_en' => 'Gopalganj', 'name_bn' => 'গোপালগঞ্জ', 'population' => 1295053],
            ['division' => '30', 'code' => '48', 'name_en' => 'Kishoreganj', 'name_bn' => 'কিশোরগঞ্জ', 'population' => 3267630],
            ['division' => '30', 'code' => '54', 'name_en' => 'Madaripur', 'name_bn' => 'মাদারীপুর', 'population' => 1293027],
            ['division' => '30', 'code' => '56', 'name_en' => 'Manikganj', 'name_bn' => 'মানিকগঞ্জ', 'population' => 1558024],
            ['division' => '30', 'code' => '59', 'name_en' => 'Munshiganj', 'name_bn' => 'মুন্সীগঞ্জ', 'population' => 1625418],
            ['division' => '30', 'code' => '67', 'name_en' => 'Narayanganj', 'name_bn' => 'নারায়ণগঞ্জ', 'population' => 3909138],
            ['division' => '30', 'code' => '68', 'name_en' => 'Narsingdi', 'name_bn' => 'নরসিংদী', 'population' => 2584452],
            ['division' => '30', 'code' => '82', 'name_en' => 'Rajbari', 'name_bn' => 'রাজবাড়ী', 'population' => 1189821],
            ['division' => '30', 'code' => '86', 'name_en' => 'Shariatpur', 'name_bn' => 'শরীয়তপুর', 'population' => 1225537],
            ['division' => '30', 'code' => '93', 'name_en' => 'Tangail', 'name_bn' => 'টাঙ্গাইল', 'population' => 4037608],

            // Khulna Division (40)
            ['division' => '40', 'code' => '1', 'name_en' => 'Bagerhat', 'name_bn' => 'বাগেরহাট', 'population' => 1613079],
            ['division' => '40', 'code' => '18', 'name_en' => 'Chuadanga', 'name_bn' => 'চুয়াডাঙ্গা', 'population' => 1234066],
            ['division' => '40', 'code' => '41', 'name_en' => 'Jashore', 'name_bn' => 'যশোর', 'population' => 3076849],
            ['division' => '40', 'code' => '44', 'name_en' => 'Jhenaidah', 'name_bn' => 'ঝিনাইদহ', 'population' => 2005849],
            ['division' => '40', 'code' => '47', 'name_en' => 'Khulna', 'name_bn' => 'খুলনা', 'population' => 2613385],
            ['division' => '40', 'code' => '50', 'name_en' => 'Kushtia', 'name_bn' => 'কুষ্টিয়া', 'population' => 2149692],
            ['division' => '40', 'code' => '55', 'name_en' => 'Magura', 'name_bn' => 'মাগুরা', 'population' => 1033115],
            ['division' => '40', 'code' => '57', 'name_en' => 'Meherpur', 'name_bn' => 'মেহেরপুর', 'population' => 705356],
            ['division' => '40', 'code' => '65', 'name_en' => 'Narail', 'name_bn' => 'নড়াইল', 'population' => 788673],
            ['division' => '40', 'code' => '87', 'name_en' => 'Satkhira', 'name_bn' => 'সাতক্ষীরা', 'population' => 2196581],

            // Mymensingh Division (45)
            ['division' => '45', 'code' => '39', 'name_en' => 'Jamalpur', 'name_bn' => 'জামালপুর', 'population' => 2499737],
            ['division' => '45', 'code' => '61', 'name_en' => 'Mymensingh', 'name_bn' => 'ময়মনসিংহ', 'population' => 5899052],
            ['division' => '45', 'code' => '72', 'name_en' => 'Netrokona', 'name_bn' => 'নেত্রকোণা', 'population' => 2324856],
            ['division' => '45', 'code' => '89', 'name_en' => 'Sherpur', 'name_bn' => 'শেরপুর', 'population' => 1501180],

            // Rajshahi Division (50)
            ['division' => '50', 'code' => '10', 'name_en' => 'Bogura', 'name_bn' => 'বগুড়া', 'population' => 3734300],
            ['division' => '50', 'code' => '38', 'name_en' => 'Joypurhat', 'name_bn' => 'জয়পুরহাট', 'population' => 956447],
            ['division' => '50', 'code' => '64', 'name_en' => 'Naogaon', 'name_bn' => 'নওগাঁ', 'population' => 2784598],
            ['division' => '50', 'code' => '69', 'name_en' => 'Natore', 'name_bn' => 'নাটোর', 'population' => 1859921],
            ['division' => '50', 'code' => '70', 'name_en' => 'Chapainawabganj', 'name_bn' => 'চাঁপাইনবাবগঞ্জ', 'population' => 1835528],
            ['division' => '50', 'code' => '76', 'name_en' => 'Pabna', 'name_bn' => 'পাবনা', 'population' => 2909622],
            ['division' => '50', 'code' => '81', 'name_en' => 'Rajshahi', 'name_bn' => 'রাজশাহী', 'population' => 2915013],
            ['division' => '50', 'code' => '88', 'name_en' => 'Sirajganj', 'name_bn' => 'সিরাজগঞ্জ', 'population' => 3357758],

            // Rangpur Division (55)
            ['division' => '55', 'code' => '27', 'name_en' => 'Dinajpur', 'name_bn' => 'দিনাজপুর', 'population' => 3315238],
            ['division' => '55', 'code' => '32', 'name_en' => 'Gaibandha', 'name_bn' => 'গাইবান্ধা', 'population' => 2562232],
            ['division' => '55', 'code' => '49', 'name_en' => 'Kurigram', 'name_bn' => 'কুড়িগ্রাম', 'population' => 2329161],
            ['division' => '55', 'code' => '52', 'name_en' => 'Lalmonirhat', 'name_bn' => 'লালমনিরহাট', 'population' => 1428406],
            ['division' => '55', 'code' => '73', 'name_en' => 'Nilphamari', 'name_bn' => 'নীলফামারী', 'population' => 2092567],
            ['division' => '55', 'code' => '77', 'name_en' => 'Panchagarh', 'name_bn' => 'পঞ্চগড়', 'population' => 1179476],
            ['division' => '55', 'code' => '85', 'name_en' => 'Rangpur', 'name_bn' => 'রংপুর', 'population' => 3169615],
            ['division' => '55', 'code' => '94', 'name_en' => 'Thakurgaon', 'name_bn' => 'ঠাকুরগাঁও', 'population' => 1533894],

            // Sylhet Division (70)
            ['division' => '70', 'code' => '36', 'name_en' => 'Habiganj', 'name_bn' => 'হবিগঞ্জ', 'population' => 2358886],
            ['division' => '70', 'code' => '58', 'name_en' => 'Moulvibazar', 'name_bn' => 'মৌলভীবাজার', 'population' => 2123445],
            ['division' => '70', 'code' => '90', 'name_en' => 'Sunamganj', 'name_bn' => 'সুনামগঞ্জ', 'population' => 2695495],
            ['division' => '70', 'code' => '91', 'name_en' => 'Sylhet', 'name_bn' => 'সিলেট', 'population' => 3857037],
        ];

        $divisionIds = [];
        foreach ($divisions as $division) {
            $divisionIds[$division['code']] = Division::create($division)->id;
        }

        foreach ($districts as $district) {
            District::create([
                'division_id' => $divisionIds[$district['division']],
                'code' => $district['code'],
                'name_en' => $district['name_en'],
                'name_bn' => $district['name_bn'],
                'slug' => Str::slug($district['name_en']),
                'population' => $district['population'],
            ]);
        }
    }
}
