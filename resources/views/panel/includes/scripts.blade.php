<script src="{{ asset('assets/lib/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets/lib/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/lib/feather-icons/feather.min.js') }}"></script>
<script type="text/javascript"
        src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
<script src="{{ asset('assets/lib/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
<script src="{{ asset('assets/lib/jquery.flot/jquery.flot.js') }}"></script>
<script src="{{ asset('assets/lib/jquery.flot/jquery.flot.stack.js') }}"></script>
<script src="{{ asset('assets/lib/jquery.flot/jquery.flot.resize.js') }}"></script>
{{--  <script src="{{ asset('assets/lib/chart.js/Chart.bundle.min.js') }}"></script>  --}}
<script src="{{ asset('assets/js/canvasjs.js') }}"></script>
<script src="{{ asset('assets/lib/jqvmap/jquery.vmap.min.js') }}"></script>
<script src="{{ asset('assets/lib/jqvmap/maps/jquery.vmap.usa.js') }}"></script>

<script src="{{ asset('assets/js/dashforge.js') }}"></script>
<script src="{{ asset('assets/js/dashforge.sampledata.js') }}"></script>

<script src="{{ asset('assets/lib/js-cookie/js.cookie.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>
<script src="{{ asset('assets/js/sound.js') }}" type="text/javascript"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.10.2/tinymce.min.js" referrerpolicy="origin"></script>
<script type="text/javascript">
        tinymce.init({
            selector: 'textarea.tinymce-editor',
            height: 400,
            menubar: true,
            plugins: [
                'advlist autolink lists link image charmap print preview anchor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table paste code help wordcount'
            ],
            toolbar: 'undo redo | formatselect | code' +
                'bold italic backcolor | alignleft aligncenter ' +
                'alignright alignjustify | bullist numlist outdent indent | ' +
                'removeformat | help',
            content_css: '//www.tiny.cloud/css/codepen.min.css'
        });
</script>
<script type="text/javascript">
    var base_url = "{{ url('') }}";

    ion.sound({
        sounds: [
            {
                name: "button_tiny"
            }
        ],
        volume: 0.5,
        path: "{{ asset('assets/js/sounds/') }}/",
        preload: true
    });

</script>

<script src="{{ asset('assets/js/printThis.js') }}"></script>
<script src="{{ asset('assets/js/scripts.js') }}"></script>
<script src="{{ asset('assets/js/jquery.datetimepicker.js') }}"></script>
<script src="{{ asset('assets/js/moment.min.js') }}"></script>
<script src="{{ asset('assets/js/datepicker.js') }}"></script>
