<hr class="mt-5 mb-1">
    <form method="POST" action="">
        <input hidden="class_id" value="{{ $class_id }}">
        {{ csrf_field() }}
        <div class="row flex-row mt-2">
            <div class="form-group col">
                <label for="name">Edit Class Name:</label>
                <input type="text" class="form-control" value="{{ $class_name }}">
            </div>
            <div class="form-group col">
                <label for="class_grade_level">Grade Level:</label>
                <select class="custom-select" name="grade_level" id="class_grade_level">
                    <option selected>Choose...</option>
                    <option value="1" {{ $class_grade_lvl == 1 ? 'selected' : '' }}>K-3 Grades (No Student login)</option>
                    <option value="2" {{ $class_grade_lvl == 2 ? 'selected' : '' }}>4-5 Grades</option>
                    {{-- <option value="3" {{ $class_grade_lvl == 3 ? 'selected' : '' }}>6-8 Grades</option> --}}
                    <option value="4" {{ $class_grade_lvl == 4 ? 'selected' : '' }}>9-12+ Grades</option>
                </select>
            </div>
        </div>
        <div class="row" style="min-height:50px">
            <div class="col-sm-9">
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-check small">
                            <input type="checkbox" name="delete_class" class="form-check-input form-toggle" id="delete_class"  data-formtoggle="form_delete_students" data-toggle="popover" data-content="<span class='text-danger'>Deleting a class cannot be undone</span>">
                            <label for="delete_class" class="form-check-label text-muted">Delete this class</label>
                        </div>
                    </div>
                    <div class="col-md-7 form-delete-class animated faster bounceInLeft" id="form_delete_students">
                        <div class="form-check small">
                            <input type="radio" name="delete_class_students" id="delete_unassign" value="unassign" checked>
                            <label for="delete_unassign" class="form-check-label text-primary">Unassign students in this class</label>
                        </div>
                        <div class="form-check small">
                            <input type="radio" name="delete_class_students" id="delete_deactivate" value="deactivate">
                            <label for="delete_deactivate" class="form-check-label text-primary">Deactivate students in this class</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-3 text-right"><input type="submit" class="btn btn-secondary btn-sm" value="Save Class Settings"></div>
        </div>
    </form>
