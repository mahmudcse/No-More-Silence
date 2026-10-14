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
            ['division' => '10', 'code' => '4', 'name_en' => 'Barguna', 'name_bn' => 'বরগুনা'],
            ['division' => '10', 'code' => '42', 'name_en' => 'Jhalakathi', 'name_bn' => 'ঝালকাঠি'],
            ['division' => '10', 'code' => '6', 'name_en' => 'Barishal', 'name_bn' => 'বরিশাল'],
            ['division' => '10', 'code' => '78', 'name_en' => 'Patuakhali', 'name_bn' => 'পটুয়াখালী'],
            ['division' => '10', 'code' => '79', 'name_en' => 'Pirojpur', 'name_bn' => 'পিরোজপুর'],
            ['division' => '10', 'code' => '9', 'name_en' => 'Bhola', 'name_bn' => 'ভোলা'],

            ['division' => '20', 'code' => '12', 'name_en' => 'Brahmanbaria', 'name_bn' => 'ব্রাহ্মণবাড়িয়া'],
            ['division' => '20', 'code' => '13', 'name_en' => 'Chandpur', 'name_bn' => 'চাঁদপুর'],
            ['division' => '20', 'code' => '15', 'name_en' => 'Chattogram', 'name_bn' => 'চট্টগ্রাম'],
            ['division' => '20', 'code' => '19', 'name_en' => 'Cumilla', 'name_bn' => 'কুমিল্লা'],
            ['division' => '20', 'code' => '22', 'name_en' => 'Coxsbazar', 'name_bn' => 'কক্সবাজার'],
            ['division' => '20', 'code' => '3', 'name_en' => 'Bandarban', 'name_bn' => 'বান্দরবান'],
            ['division' => '20', 'code' => '30', 'name_en' => 'Feni', 'name_bn' => 'ফেনী'],
            ['division' => '20', 'code' => '46', 'name_en' => 'Khagrachari', 'name_bn' => 'খাগড়াছড়ি'],
            ['division' => '20', 'code' => '51', 'name_en' => 'Lakshmipur', 'name_bn' => 'লক্ষ্মীপুর'],
            ['division' => '20', 'code' => '75', 'name_en' => 'Noakhali', 'name_bn' => 'নোয়াখালী'],
            ['division' => '20', 'code' => '84', 'name_en' => 'Rangamati', 'name_bn' => 'রাঙ্গামাটি পার্বত্য'],

            ['division' => '30', 'code' => '26', 'name_en' => 'Dhaka', 'name_bn' => 'ঢাকা'],
            ['division' => '30', 'code' => '29', 'name_en' => 'Faridpur', 'name_bn' => 'ফরিদপুর'],
            ['division' => '30', 'code' => '33', 'name_en' => 'Gazipur', 'name_bn' => 'গাজীপুর'],
            ['division' => '30', 'code' => '35', 'name_en' => 'Gopalganj', 'name_bn' => 'গোপালগঞ্জ'],
            ['division' => '30', 'code' => '48', 'name_en' => 'Kishoreganj', 'name_bn' => 'কিশোরগঞ্জ'],
            ['division' => '30', 'code' => '54', 'name_en' => 'Madaripur', 'name_bn' => 'মাদারীপুর'],
            ['division' => '30', 'code' => '56', 'name_en' => 'Manikganj', 'name_bn' => 'মানিকগঞ্জ'],
            ['division' => '30', 'code' => '59', 'name_en' => 'Munshiganj', 'name_bn' => 'মুন্সীগঞ্জ'],
            ['division' => '30', 'code' => '67', 'name_en' => 'Narayanganj', 'name_bn' => 'নারায়ণগঞ্জ'],
            ['division' => '30', 'code' => '68', 'name_en' => 'Narsingdi', 'name_bn' => 'নরসিংদী'],
            ['division' => '30', 'code' => '82', 'name_en' => 'Rajbari', 'name_bn' => 'রাজবাড়ী'],
            ['division' => '30', 'code' => '86', 'name_en' => 'Shariatpur', 'name_bn' => 'শরীয়তপুর'],
            ['division' => '30', 'code' => '93', 'name_en' => 'Tangail', 'name_bn' => 'টাঙ্গাইল'],

            ['division' => '40', 'code' => '1', 'name_en' => 'Bagerhat', 'name_bn' => 'বাগেরহাট'],
            ['division' => '40', 'code' => '18', 'name_en' => 'Chuadanga', 'name_bn' => 'চুয়াডাঙ্গা'],
            ['division' => '40', 'code' => '41', 'name_en' => 'Jashore', 'name_bn' => 'যশোর'],
            ['division' => '40', 'code' => '44', 'name_en' => 'Jhenaidah', 'name_bn' => 'ঝিনাইদহ'],
            ['division' => '40', 'code' => '47', 'name_en' => 'Khulna', 'name_bn' => 'খুলনা'],
            ['division' => '40', 'code' => '50', 'name_en' => 'Kushtia', 'name_bn' => 'কুষ্টিয়া'],
            ['division' => '40', 'code' => '55', 'name_en' => 'Magura', 'name_bn' => 'মাগুরা'],
            ['division' => '40', 'code' => '57', 'name_en' => 'Meherpur', 'name_bn' => 'মেহেরপুর'],
            ['division' => '40', 'code' => '65', 'name_en' => 'Narail', 'name_bn' => 'নড়াইল'],
            ['division' => '40', 'code' => '87', 'name_en' => 'Satkhira', 'name_bn' => 'সাতক্ষীরা'],

            ['division' => '45', 'code' => '39', 'name_en' => 'Jamalpur', 'name_bn' => 'জামালপুর'],
            ['division' => '45', 'code' => '61', 'name_en' => 'Mymensingh', 'name_bn' => 'ময়মনসিংহ'],
            ['division' => '45', 'code' => '72', 'name_en' => 'Netrokona', 'name_bn' => 'নেত্রকোণা'],
            ['division' => '45', 'code' => '89', 'name_en' => 'Sherpur', 'name_bn' => 'শেরপুর'],

            ['division' => '50', 'code' => '10', 'name_en' => 'Bogura', 'name_bn' => 'বগুড়া'],
            ['division' => '50', 'code' => '38', 'name_en' => 'Joypurhat', 'name_bn' => 'জয়পুরহাট'],
            ['division' => '50', 'code' => '64', 'name_en' => 'Naogaon', 'name_bn' => 'নওগাঁ'],
            ['division' => '50', 'code' => '69', 'name_en' => 'Natore', 'name_bn' => 'নাটোর'],
            ['division' => '50', 'code' => '70', 'name_en' => 'Chapainawabganj', 'name_bn' => 'চাঁপাইনবাবগঞ্জ'],
            ['division' => '50', 'code' => '76', 'name_en' => 'Pabna', 'name_bn' => 'পাবনা'],
            ['division' => '50', 'code' => '81', 'name_en' => 'Rajshahi', 'name_bn' => 'রাজশাহী'],
            ['division' => '50', 'code' => '88', 'name_en' => 'Sirajganj', 'name_bn' => 'সিরাজগঞ্জ'],

            ['division' => '55', 'code' => '27', 'name_en' => 'Dinajpur', 'name_bn' => 'দিনাজপুর'],
            ['division' => '55', 'code' => '32', 'name_en' => 'Gaibandha', 'name_bn' => 'গাইবান্ধা'],
            ['division' => '55', 'code' => '49', 'name_en' => 'Kurigram', 'name_bn' => 'কুড়িগ্রাম'],
            ['division' => '55', 'code' => '52', 'name_en' => 'Lalmonirhat', 'name_bn' => 'লালমনিরহাট'],
            ['division' => '55', 'code' => '73', 'name_en' => 'Nilphamari', 'name_bn' => 'নীলফামারী'],
            ['division' => '55', 'code' => '77', 'name_en' => 'Panchagarh', 'name_bn' => 'পঞ্চগড়'],
            ['division' => '55', 'code' => '85', 'name_en' => 'Rangpur', 'name_bn' => 'রংপুর'],
            ['division' => '55', 'code' => '94', 'name_en' => 'Thakurgaon', 'name_bn' => 'ঠাকুরগাঁও'],

            ['division' => '70', 'code' => '36', 'name_en' => 'Habiganj', 'name_bn' => 'হবিগঞ্জ'],
            ['division' => '70', 'code' => '58', 'name_en' => 'Moulvibazar', 'name_bn' => 'মৌলভীবাজার'],
            ['division' => '70', 'code' => '90', 'name_en' => 'Sunamganj', 'name_bn' => 'সুনামগঞ্জ'],
            ['division' => '70', 'code' => '91', 'name_en' => 'Sylhet', 'name_bn' => 'সিলেট'],
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
                'population' => null,
            ]);
        }
    }
}
