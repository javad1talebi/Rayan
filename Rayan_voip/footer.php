 <!-- /.content-wrapper -->
 <footer class="main-footer">
    <strong><a href="">ارتباط گستر افق رایان</a>.</strong>
  </footer>

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="/Rayan_voip/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="/Rayan_voip/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- Select2 -->
<script src="/Rayan_voip/plugins/select2/select2.full.min.js"></script>
<!-- InputMask -->
<script src="/Rayan_voip/plugins/input-mask/jquery.inputmask.js"></script>
<script src="/Rayan_voip/plugins/input-mask/jquery.inputmask.date.extensions.js"></script>
<script src="/Rayan_voip/plugins/input-mask/jquery.inputmask.extensions.js"></script>
<!-- date-range-picker -->
<script src="/Rayan_voip/assets/js//moment.min.js"></script>
<script src="/Rayan_voip/plugins/daterangepicker/daterangepicker.js"></script>
<!-- bootstrap color picker -->
<script src="/Rayan_voip/plugins/colorpicker/bootstrap-colorpicker.min.js"></script>
<!-- SlimScroll 1.3.0 -->
<script src="/Rayan_voip/plugins/slimScroll/jquery.slimscroll.min.js"></script>
<!-- iCheck 1.0.1 -->
<script src="/Rayan_voip/plugins/iCheck/icheck.min.js"></script>
<!-- FastClick -->
<script src="/Rayan_voip/plugins/fastclick/fastclick.js"></script>
<!-- Persian Data Picker -->
<script src="/Rayan_voip/dist/js/persian-date.min.js"></script>
<script src="/Rayan_voip/dist/js/persian-datepicker.min.js"></script>
<!-- AdminLTE App -->
<script src="/Rayan_voip/dist/js/adminlte.min.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="/Rayan_voip/dist/js/demo.js"></script>
<!-- Page script -->






<!-- ChartJS 1.0.1 -->
<script src="/Rayan_voip/plugins/chartjs-old/Chart.min.js"></script>

<!-- FLOT CHARTS -->
<script src="/Rayan_voip/plugins/flot/jquery.flot.min.js"></script>
<!-- FLOT RESIZE PLUGIN - allows the chart to redraw when the window is resized -->
<script src="/Rayan_voip/plugins/flot/jquery.flot.resize.min.js"></script>
<!-- FLOT PIE PLUGIN - also used to draw donut charts -->
<script src="/Rayan_voip/plugins/flot/jquery.flot.pie.min.js"></script>
<!-- FLOT CATEGORIES PLUGIN - Used to draw bar charts -->
<script src="/Rayan_voip/plugins/flot/jquery.flot.categories.min.js"></script>
<!-- page script -->

<script src="/Rayan_voip/assets/css/sweetalert2@11"></script>
<script>
  $(function () {
    //Initialize Select2 Elements
    $('.select2').select2()

    //Datemask dd/mm/yyyy
    $('#datemask').inputmask('dd/mm/yyyy', { 'placeholder': 'dd/mm/yyyy' })
    //Datemask2 mm/dd/yyyy
    $('#datemask2').inputmask('mm/dd/yyyy', { 'placeholder': 'mm/dd/yyyy' })
    //Money Euro
    $('[data-mask]').inputmask()

    //iCheck for checkbox and radio inputs
    $('input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
      checkboxClass: 'icheckbox_minimal-blue',
      radioClass   : 'iradio_minimal-blue'
    })
    //Red color scheme for iCheck
    $('input[type="checkbox"].minimal-red, input[type="radio"].minimal-red').iCheck({
      checkboxClass: 'icheckbox_minimal-red',
      radioClass   : 'iradio_minimal-red'
    })
    //Flat red color scheme for iCheck
    $('input[type="checkbox"].flat-red, input[type="radio"].flat-red').iCheck({
      checkboxClass: 'icheckbox_flat-green',
      radioClass   : 'iradio_flat-green'
    })

    //Colorpicker
    $('.my-colorpicker1').colorpicker()
    //color picker with addon
    $('.my-colorpicker2').colorpicker()


    $('.normal-example').persianDatepicker();




  })
 
</script>
</body>


