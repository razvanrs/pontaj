<?php

namespace App\Imports;

use App\Models\SchoolingPeriod;
use App\Models\Student;
use App\Models\StudentClass;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentsImport implements ToModel, WithHeadingRow
{
	/**
	 * @param array $row
	 *
	 * @return \Illuminate\Database\Eloquent\Model|null
	 */
	public function model(array $row)
	{
		return new Student([
			'erp_id' => $row['erp_id'],
			'first_name' => $row['first_name'],
			'last_name' => $row['last_name'],
			'father_first_name' => $row['father_first_name'],
			'mother_first_name' => $row['mother_first_name'],
			'erp_student_class_id' => $row['erp_student_class_id'],
			'student_class_id' => StudentClass::where('erp_id', $row['erp_student_class_id'])->first()->id,
			'erp_schooling_period_id' => $row['erp_schooling_period_id'],
			'schooling_period_id' => SchoolingPeriod::where('erp_id', $row['erp_schooling_period_id'])->first()->id,
			'birthday' => \Carbon\Carbon::parse($row['birthday'])->format('Y-m-d'),
			'birth_county_id' => $row['birth_county_id'],
			'birth_town' => $row['birth_town'],
			'domicile_county_id' => $row['domicile_county_id'],
			'domicile_town' => $row['domicile_town'],
			'selection_county_id' => $row['selection_county_id'],
			'residence_county_id' => $row['residence_county_id'],
			'residence_town' => $row['residence_town'],
			'practice_county_id' => $row['practice_county_id'],
			'practice_town' => $row['practice_town'],
			'address' => $row['address'],
			'matriculation_number' => $row['matriculation_number'],
			'ethnicity_id' => $row['ethnicity_id'],
			'sex_id' => $row['sex_id'],
			'language_id' => $row['language_id'],
			'identity_card_series' => $row['identity_card_series'],
			'identity_card_number' => $row['identity_card_number'],
			'marital_status_id' => $row['marital_status_id'],
			'admission_exam_code' => $row['admission_exam_code'],
			'admission_exam_score' => $row['admission_exam_score'],
			'bac_grades_average' => $row['bac_grades_average'],
			'car_brand' => $row['car_brand'],
			'car_registration_number' => $row['car_registration_number'],
			'deleted_at' => optional($row['deleted_at'])->map(function ($value) {
				return \Carbon\Carbon::createFromFormat('d.m.Y', $value)->format('Y-m-d');
			}),
		]);
	}
}
