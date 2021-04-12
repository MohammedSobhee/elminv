<?php

// @formatter:off
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App{
/**
 * App\CourseSettings
 *
 * @property int $id
 * @property int $teacher_id
 * @property string $name
 * @property string $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CourseSettings newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CourseSettings newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CourseSettings query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CourseSettings whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CourseSettings whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CourseSettings whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CourseSettings whereTeacherId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CourseSettings whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CourseSettings whereValue($value)
 */
	class CourseSettings extends \Eloquent {}
}

namespace App{
/**
 * App\WorksheetFormFields
 *
 * @property int $id
 * @property string $heading
 * @property string $question
 * @property string $description
 * @property string $value
 * @property int $type
 * @property int $display_size
 * @property int $status
 * @property string $timestamp
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetFormFields newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetFormFields newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetFormFields query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetFormFields whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetFormFields whereDisplaySize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetFormFields whereHeading($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetFormFields whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetFormFields whereQuestion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetFormFields whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetFormFields whereTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetFormFields whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetFormFields whereValue($value)
 */
	class WorksheetFormFields extends \Eloquent {}
}

namespace App{
/**
 * App\Projects
 *
 * @property int $id
 * @property int $class_id
 * @property int $school_id
 * @property string $project_name
 * @property int $groups
 * @property int $status
 * @property int|null $last_updated_by
 * @property int|null $team_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ProjectMembers[] $members
 * @property-read int|null $members_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Projects newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Projects newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Projects query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Projects whereClassId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Projects whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Projects whereGroups($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Projects whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Projects whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Projects whereProjectName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Projects whereSchoolId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Projects whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Projects whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Projects whereUpdatedAt($value)
 */
	class Projects extends \Eloquent {}
}

namespace App{
/**
 * App\SchoolSettings
 *
 * @property int $id
 * @property int $school_id
 * @property int $type
 * @property int|null $max_classes
 * @property int|null $students_per_class
 * @property string $contract_expiration_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SchoolSettings newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SchoolSettings newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SchoolSettings query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SchoolSettings whereContractExpirationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SchoolSettings whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SchoolSettings whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SchoolSettings whereMaxClasses($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SchoolSettings whereSchoolId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SchoolSettings whereStudentsPerClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SchoolSettings whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SchoolSettings whereUpdatedAt($value)
 */
	class SchoolSettings extends \Eloquent {}
}

namespace App{
/**
 * App\Grades
 *
 * @property int $id
 * @property int $type
 * @property int $type_id
 * @property int $category_id
 * @property int|null $project_id
 * @property int $user_id
 * @property int|null $points
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $message
 * @property int|null $message_id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Grades newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Grades newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Grades query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Grades whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Grades whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Grades whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Grades whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Grades whereMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Grades wherePoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Grades whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Grades whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Grades whereTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Grades whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Grades whereUserId($value)
 */
	class Grades extends \Eloquent {}
}

namespace App{
/**
 * App\AssignmentSubmitted
 *
 * @property int $id
 * @property int $type
 * @property int|null $status
 * @property int $type_id
 * @property int|null $project_id
 * @property int $user_id
 * @property string|null $comments
 * @property int|null $message_id
 * @property string|null $file_name
 * @property string|null $file_location
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AssignmentSubmitted newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AssignmentSubmitted newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AssignmentSubmitted query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AssignmentSubmitted whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AssignmentSubmitted whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AssignmentSubmitted whereFileLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AssignmentSubmitted whereFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AssignmentSubmitted whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AssignmentSubmitted whereMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AssignmentSubmitted whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AssignmentSubmitted whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AssignmentSubmitted whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AssignmentSubmitted whereTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AssignmentSubmitted whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AssignmentSubmitted whereUserId($value)
 */
	class AssignmentSubmitted extends \Eloquent {}
}

namespace App{
/**
 * App\WorksheetAnswers
 *
 * @property int $id
 * @property int $worksheet_id
 * @property string $form_field_id
 * @property int $project_id
 * @property string $answer
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetAnswers newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetAnswers newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetAnswers query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetAnswers whereAnswer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetAnswers whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetAnswers whereFormFieldId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetAnswers whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetAnswers whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetAnswers whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetAnswers whereWorksheetId($value)
 */
	class WorksheetAnswers extends \Eloquent {}
}

namespace App{
/**
 * App\Classes
 *
 * @property int $id
 * @property int|null $school_id
 * @property string $class_name
 * @property int $class_type
 * @property string|null $student_code
 * @property string|null $assistant_teacher_code
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $message
 * @property-read mixed $updated
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ClassMembers[] $members
 * @property-read int|null $members_count
 * @property-read \App\ClassType $type
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Classes newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Classes newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Classes query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Classes whereAssistantTeacherCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Classes whereClassName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Classes whereClassType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Classes whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Classes whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Classes whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Classes whereSchoolId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Classes whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Classes whereStudentCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Classes whereUpdatedAt($value)
 */
	class Classes extends \Eloquent {}
}

namespace App{
/**
 * App\User
 *
 * @property int $id
 * @property string $name
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string $email
 * @property int|null $school_id
 * @property string|null $subject
 * @property string|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property int|null $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $message
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Chat[] $chats
 * @property-read int|null $chats_count
 * @property-read mixed $full_name
 * @property-read mixed $full_name_reversed
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Permission[] $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Role[] $roles
 * @property-read int|null $roles_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereSchoolId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

namespace App{
/**
 * App\Role
 *
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Permission[] $permissions
 * @property-read int|null $permissions_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Role query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Role whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Role whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Role whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Role whereUpdatedAt($value)
 */
	class Role extends \Eloquent {}
}

namespace App{
/**
 * App\RubricSettings
 *
 * @property int $id
 * @property int $category_id
 * @property int $teacher_id
 * @property int $active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\RubricCategories $cats
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RubricSettings newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RubricSettings newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RubricSettings query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RubricSettings whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RubricSettings whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RubricSettings whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RubricSettings whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RubricSettings whereTeacherId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RubricSettings whereUpdatedAt($value)
 */
	class RubricSettings extends \Eloquent {}
}

namespace App{
/**
 * App\ProjectMembers
 *
 * @property int $id
 * @property int $user_id
 * @property int $project_id
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProjectMembers newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProjectMembers newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProjectMembers query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProjectMembers whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProjectMembers whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProjectMembers whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProjectMembers whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProjectMembers whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProjectMembers whereUserId($value)
 */
	class ProjectMembers extends \Eloquent {}
}

namespace App{
/**
 * App\AssignmentClasses
 *
 * @property int $id
 * @property int $class_id
 * @property int|null $assignment_id
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AssignmentClasses newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AssignmentClasses newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AssignmentClasses query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AssignmentClasses whereAssignmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AssignmentClasses whereClassId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AssignmentClasses whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AssignmentClasses whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AssignmentClasses whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AssignmentClasses whereUpdatedAt($value)
 */
	class AssignmentClasses extends \Eloquent {}
}

namespace App{
/**
 * App\TeamMembers
 *
 * @property int $id
 * @property int $team_id
 * @property int $user_id
 * @property int|null $class_id
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $updated
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TeamMembers newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TeamMembers newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TeamMembers query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TeamMembers whereClassId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TeamMembers whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TeamMembers whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TeamMembers whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TeamMembers whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TeamMembers whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TeamMembers whereUserId($value)
 */
	class TeamMembers extends \Eloquent {}
}

namespace App{
/**
 * App\ClassType
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClassType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClassType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClassType query()
 */
	class ClassType extends \Eloquent {}
}

namespace App{
/**
 * App\SchoolContactInfo
 *
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $phone
 * @property string|null $extension
 * @property int $school_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SchoolContactInfo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SchoolContactInfo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SchoolContactInfo query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SchoolContactInfo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SchoolContactInfo whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SchoolContactInfo whereExtension($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SchoolContactInfo whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SchoolContactInfo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SchoolContactInfo whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SchoolContactInfo wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SchoolContactInfo whereSchoolId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SchoolContactInfo whereUpdatedAt($value)
 */
	class SchoolContactInfo extends \Eloquent {}
}

namespace App{
/**
 * App\ClassMembers
 *
 * @property int $id
 * @property int|null $class_id
 * @property int $user_id
 * @property int $role_id
 * @property int $active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClassMembers newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClassMembers newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClassMembers query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClassMembers whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClassMembers whereClassId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClassMembers whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClassMembers whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClassMembers whereRoleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClassMembers whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClassMembers whereUserId($value)
 */
	class ClassMembers extends \Eloquent {}
}

namespace App{
/**
 * App\RubricCategories
 *
 * @property int $id
 * @property string $category_name
 * @property int $category_value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RubricCategories newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RubricCategories newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RubricCategories query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RubricCategories whereCategoryName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RubricCategories whereCategoryValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RubricCategories whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RubricCategories whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RubricCategories whereUpdatedAt($value)
 */
	class RubricCategories extends \Eloquent {}
}

namespace App{
/**
 * App\MessagesType
 *
 * @property int $id
 * @property string $name
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\MessagesType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\MessagesType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\MessagesType query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\MessagesType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\MessagesType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\MessagesType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\MessagesType whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\MessagesType whereUpdatedAt($value)
 */
	class MessagesType extends \Eloquent {}
}

namespace App{
/**
 * App\Schools
 *
 * @property int $id
 * @property string $name
 * @property int $status
 * @property string|null $school_code
 * @property string|null $student_code
 * @property string|null $teacher_code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\SchoolContactInfo $contact
 * @property-read \App\SchoolSettings $settings
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\User[] $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schools newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schools newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schools query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schools whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schools whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schools whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schools whereSchoolCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schools whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schools whereStudentCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schools whereTeacherCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schools whereUpdatedAt($value)
 */
	class Schools extends \Eloquent {}
}

namespace App{
/**
 * App\WorksheetRubric
 *
 * @property int $id
 * @property int $category_id
 * @property string $category_name
 * @property int $order
 * @property int $category_value
 * @property int $teacher_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $sent_to_teacher
 * @property int|null $active
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetRubric newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetRubric newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetRubric query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetRubric whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetRubric whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetRubric whereCategoryName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetRubric whereCategoryValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetRubric whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetRubric whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetRubric whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetRubric whereSentToTeacher($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetRubric whereTeacherId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetRubric whereUpdatedAt($value)
 */
	class WorksheetRubric extends \Eloquent {}
}

namespace App{
/**
 * App\WorksheetFormFieldGroups
 *
 * @property int $id
 * @property int|null $group_id
 * @property int $worksheet_id
 * @property int $form_field_id
 * @property int|null $type
 * @property int $order
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetFormFieldGroups newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetFormFieldGroups newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetFormFieldGroups query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetFormFieldGroups whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetFormFieldGroups whereFormFieldId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetFormFieldGroups whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetFormFieldGroups whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetFormFieldGroups whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetFormFieldGroups whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetFormFieldGroups whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetFormFieldGroups whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorksheetFormFieldGroups whereWorksheetId($value)
 */
	class WorksheetFormFieldGroups extends \Eloquent {}
}

namespace App{
/**
 * App\Assignment
 *
 * @property int $id
 * @property string $assignment_name
 * @property string|null $file_name
 * @property string|null $file_location
 * @property int $user_id
 * @property int|null $category_id
 * @property int|null $points
 * @property int $status
 * @property int $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Assignment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Assignment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Assignment query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Assignment whereAssignmentName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Assignment whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Assignment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Assignment whereFileLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Assignment whereFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Assignment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Assignment wherePoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Assignment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Assignment whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Assignment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Assignment whereUserId($value)
 */
	class Assignment extends \Eloquent {}
}

namespace App{
/**
 * App\Chat
 *
 * @property int $id
 * @property int $user_id
 * @property string $message
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Chat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Chat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Chat query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Chat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Chat whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Chat whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Chat whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Chat whereUserId($value)
 */
	class Chat extends \Eloquent {}
}

namespace App{
/**
 * App\Worksheet
 *
 * @property int $id
 * @property string $form_fields_ids
 * @property string $title
 * @property int $file
 * @property int $example
 * @property int $status
 * @property int $order
 * @property int|null $default_point_value
 * @property string $timestamp
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\WorksheetFormFieldGroups[] $groups
 * @property-read int|null $groups_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Worksheet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Worksheet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Worksheet query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Worksheet whereDefaultPointValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Worksheet whereExample($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Worksheet whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Worksheet whereFormFieldsIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Worksheet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Worksheet whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Worksheet whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Worksheet whereTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Worksheet whereTitle($value)
 */
	class Worksheet extends \Eloquent {}
}

namespace App{
/**
 * App\Permission
 *
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Role[] $roles
 * @property-read int|null $roles_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Permission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Permission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Permission query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Permission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Permission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Permission whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Permission whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Permission whereUpdatedAt($value)
 */
	class Permission extends \Eloquent {}
}

namespace App{
/**
 * App\Messages
 *
 * @property int $id
 * @property int $type
 * @property int $recipient_id
 * @property int $sender_id
 * @property string|null $title
 * @property string|null $content
 * @property int|null $archive
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $updated
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Messages newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Messages newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Messages query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Messages whereArchive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Messages whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Messages whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Messages whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Messages whereRecipientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Messages whereSenderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Messages whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Messages whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Messages whereUpdatedAt($value)
 */
	class Messages extends \Eloquent {}
}

namespace App{
/**
 * App\Teams
 *
 * @property int $id
 * @property string $team_name
 * @property int|null $class_id
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $message
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\TeamMembers[] $members
 * @property-read int|null $members_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Teams newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Teams newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Teams query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Teams whereClassId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Teams whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Teams whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Teams whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Teams whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Teams whereTeamName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Teams whereUpdatedAt($value)
 */
	class Teams extends \Eloquent {}
}
