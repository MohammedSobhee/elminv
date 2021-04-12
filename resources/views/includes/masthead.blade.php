<div class="masthead @yield('masthead-margin')">
  <div class="page-title-wrapper">
    <div class="container">
      <div class="row">
        <div class="col-lg-9">
            <h1 class="page-title pl-0 d-flex align-items-center">
            @yield('title')
            @hasSection('title-helper')
              <span class="page-title-helper">@yield('title-helper')</span>
            @endif
          </h1>
          </div>
            @if(Auth::check() && isset($usersess) && auth()->user()->school->status)
              @role('teacher', 'school-admin', 'assistant-teacher')
                @if(count($usersess->courseware_types) > 1)
                    <div class="col-lg-3 d-flex justify-content-end">
                        <form class="align-self-center w-100">
                            <select class="custom-select select-url-change custom-select-courseware custom-select-light-arrow">
                                <option>Select Courseware...</option>
                                @foreach($usersess->courseware_types as $key => $value)
                                <option value="/switchclasstype/{{$key}}" @if($usersess->class_type == $key)selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                @else
                    <div class="col-lg-3 d-flex justify-content-end">
                        <span class="page-tagline align-self-center">
                            {{-- Temp fix --}}
                            @if(array_key_exists($usersess->class_type, $usersess->courseware_types))
                                {{ $usersess->courseware_types[$usersess->class_type] }}
                            @else
                                Elementary Courseware
                            @endif
                        </span>
                    </div>
                @endif
              @endrole
            @endif
      </div>
    </div>
  </div>
</div>
