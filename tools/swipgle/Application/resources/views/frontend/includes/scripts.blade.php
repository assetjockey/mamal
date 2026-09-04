@stack('top_scripts')
<script src="{{ asset('assets/vendor/libs/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/jqueryloadingoverlay/loadingoverlay.min.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/toastr/js/vironeer-toastr.min.js') }}"></script>
@stack('scripts_libs')
<script src="{{ asset('assets/js/application.js') }}"></script>
<script src="{{ asset('assets/js/extra/extra.js') }}"></script>
@stack('scripts')
@toastrRender
