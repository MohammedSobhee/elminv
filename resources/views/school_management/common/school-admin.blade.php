@if(count($school_users))
<h4>{{ auth()->user()->school->name }} Members <a href="/dashboard/schooltree" class="small text-muted">(View School Member Tree)</a></h4>
<edit-member
    :users='@json($school_users)'
    :can_school_admin="true"
    :enable_search='@json(true)'
    class="mt-3 pb-3">
</edit-member>
@endif


<hr id="add-members" class="mt-5 mb-2">
<h4 class="m-0">Activation Codes:</h4>
<view-codes
    host="{{ Request::getHost() }}"
    user_role="{{ $usersess->role }}"
    :class_type="0"
    school_code="{{ $school_code }}"
    teacher_code="{{ $teacher_code }}">
</view-codes>
<p class="small mt-3"><a :href="`/upload/accounts`">Manually add or upload accounts <i class="fas fa-angle-right ml-1"></i></a></p>

<hr class="mt-5 mb-2">
<h4 class="m-0">Send teachers their activation codes:</h4>
<form action="/activate/send" method="post">
{{ csrf_field() }}
    <div class="form-group">
        <label class="sr-only" for="inlineFormInputName2">Email</label>
        <textarea type="text" name="email" class="form-control form-control-sm mb-2 mr-sm-2" size="40" rows="3" placeholder="Email addresses"></textarea>
        <small id="passwordHelpInline" class="form-text text-muted">
            Separate emails with commas or new lines.
        </small>
    </div>
    <input type="hidden" name="url" value="/activate/teacher">
    <input type="hidden" name="code" value="{{ $teacher_code }}">
    <input type="hidden" name="school_id" value="{{ auth()->user()->school_id }}">
    <button type="submit" class="btn btn-sm btn-light mb-2 mt-1">Send Codes</button>
</form>
