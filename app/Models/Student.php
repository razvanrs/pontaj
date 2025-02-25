<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

/**
 * @property string $full_name
 */
class Student extends Model
{
    use HasFactory;

    const MALE = 1;
    const FEMALE = 2;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'student_class_id',
        'first_name',
        'last_name',
        'father_first_name',
        'mother_first_name',
        'birthday',
        'birth_county_id',
        'birth_town',
        'domicile_county_id',
        'domicile_town',
        'selection_county_id',
        'residence_county_id',
        'residence_town',
        'practice_county_id',
        'practice_town',
        'address',
        'matriculation_number',
        'ethnicity_id',
        'sex_id',
        'foreign_language_id',
        'identity_card_series',
        'identity_card_number',
        'marital_status_id',
        'admission_exam_code',
        'admission_exam_score',
        'bac_grades_average',
        'bac_romanian_language_grade',
        'bac_main_subject_profile_grade',
        'bac_subject_of_choice_profile_grade',
        'high_school_avg_grade_for_1st_foreign_lang',
        'high_school_avg_grade_for_2nd_foreign_lang',
        'car_brand',
        'car_registration_number',
        'erp_student_class_id',
        'erp_schooling_period_id',
        'erp_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'birthday' => 'date',
        'birth_county_id' => 'integer',
        'domicile_county_id' => 'integer',
        'selection_county_id' => 'integer',
        'residence_county_id' => 'integer',
        'practice_county_id' => 'integer',
        'ethnicity_id' => 'integer',
        'sex_id' => 'integer',
        'foreign_language_id' => 'integer',
        'marital_status_id' => 'integer',
        'admission_exam_score' => 'decimal:2',
        'bac_grades_average' => 'decimal:2',
        'bac_romanian_language_grade' => 'decimal:2',
        'bac_main_subject_profile_grade' => 'decimal:2',
        'bac_subject_of_choice_profile_grade' => 'decimal:2',
        'high_school_avg_grade_for_1st_foreign_lang' => 'decimal:2',
        'high_school_avg_grade_for_2nd_foreign_lang' => 'decimal:2',
        'erp_student_class_id' => 'integer',
        'erp_schooling_period_id' => 'integer',
        'erp_id' => 'integer',
    ];


    public function sanctions(): BelongsToMany
    {
        return $this->belongsToMany(Sanction::class, 'student_sanction', 'student_id', 'sanction_id')
            ->withPivot(['id', 'date', 'user_id'])
            ->using(StudentSanction::class)
            ->withTimestamps();
    }

    public function latestSanctions(): BelongsToMany
    {
        return $this->sanctions()
            ->orderByPivot('date', 'desc');
    }

    public function studentClass(): BelongsTo
    {
        return $this->belongsTo(StudentClass::class, 'student_class_id', 'id');
    }

    public function birthCounty(): BelongsTo
    {
        return $this->belongsTo(County::class, 'birth_county_id', 'id');
    }

    public function domicileCounty(): BelongsTo
    {
        return $this->belongsTo(County::class, 'domicile_county_id', 'id');
    }

    public function selectionCounty(): BelongsTo
    {
        return $this->belongsTo(County::class, 'selection_county_id', 'id');
    }

    public function residenceCounty(): BelongsTo
    {
        return $this->belongsTo(County::class, 'residence_county_id', 'id');
    }

    public function practiceCounty(): BelongsTo
    {
        return $this->belongsTo(County::class, 'practice_county_id', 'id');
    }

    public function ethnicity(): BelongsTo
    {
        return $this->belongsTo(Ethnicity::class, 'ethnicity_id', 'id');
    }

    public function sex(): BelongsTo
    {
        return $this->belongsTo(Sex::class, 'sex_id', 'id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'foreign_language_id', 'id');
    }

    public function maritalStatus(): BelongsTo
    {
        return $this->belongsTo(MaritalStatus::class, 'marital_status_id', 'id');
    }

    public function erpStudentClass(): BelongsTo
    {
        return $this->belongsTo(StudentClass::class, 'erp_student_class_id', 'erp_id');
    }

    public function erpSchoolingPeriod(): BelongsTo
    {
        return $this->belongsTo(SchoolingPeriod::class, 'erp_schooling_period_id', 'erp_id');
    }

    public function formTeacher(): HasOneThrough
    {
        return $this->hasOneThrough(Teacher::class, StudentClass::class, 'id', 'id', 'student_class_id', 'teacher_id');
    }
}
