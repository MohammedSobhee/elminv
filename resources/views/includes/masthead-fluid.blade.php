<div class="masthead @yield('masthead-margin')">
  <div class="page-title-wrapper">
    <div class="container-fluid">
      <div class="row">
        <div class="col-lg-9">
            <h1 class="page-title pl-2">
            @yield('title')
            @hasSection('title-helper')
              <span class="page-title-helper">@yield('title-helper')</span>
            @endif
          </h1>
          </div>
      </div>
    </div>
  </div>
</div>
