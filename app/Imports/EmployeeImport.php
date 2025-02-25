<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EmployeeImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        try {
            $name = Str::of($row['name'])->lower()->trim()->replace(' ', '')->replace('-', '.')->ascii();
            $surname = Str::of($row['surname'])->lower()->trim()->replace(' ', '')->replace('-', '.')->ascii();

            // Use regex to get only the first part of the surname
            preg_match('/^[^\s.-]+/', $surname, $matches);
            $firstSurname = $matches[0];

            $user = User::create([
                'name' => $row['full_name'],
                'email' => "{$name}.{$firstSurname}@sapvlc.internet",
                'password' => bcrypt('Scoala2024!'),
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }

        $phoneNumbers = [
            'fix' => isset($row['phone_fix']) ? Str::of($row['phone_fix'])->trim() : '',
            'int' => isset($row['phone_int']) ? Str::of($row['phone_int'])->trim() : '',
            'mobile' => [
                isset($row['phone_mobile1']) ? Str::of($row['phone_mobile1'])->trim() : '',
                isset($row['phone_mobile2']) ? Str::of($row['phone_mobile2'])->trim() : '',
                isset($row['phone_mobile3']) ? Str::of($row['phone_mobile3'])->trim() : '',
            ],
        ];

        try {
            $employee = new Employee([
                'user_id' => $user->id,
                'erp_id' => $row["erp_id"],
                'social_number' => $row["social_number"],
                'name' => $row["name"],
                'surname' => $row["surname"],
                'full_name' => $row["name"] . " " . $row["surname"],
                'phone_numbers' => json_encode($phoneNumbers),
                'military_rank_type_id' => $row['military_rank_type_id'],
                'military_rank' => $row['military_rank'],
                'birthday' => $row['birthday'] ? \Carbon\Carbon::parse($row['birthday'])->format('Y-m-d') : '1970-01-01',
                'sex_id' => $row['sex_id'],
                'father_surname' => $row['father_surname'],
                'address' => $row['address'],
            ]);
        } catch (\Throwable $th) {
            ray($row);
            throw $th;
        }

        return $employee;
    }
}
