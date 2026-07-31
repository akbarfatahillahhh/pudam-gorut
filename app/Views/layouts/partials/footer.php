</div>
<script src="<?= base_url() ?>/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url() ?>/assets/js/dashboard.js"></script>
<script src="<?= base_url() ?>/assets/js/app.min.js"></script>
<!-- <script src="<?= base_url() ?>/assets/js/sidebarmenu.js"></script> -->
<!-- <script src="<?= base_url() ?>/assets/libs/jquery/dist/jquery.min.js"></script> -->
<!-- <script src="<?= base_url() ?>/assets/libs/apexcharts/dist/apexcharts.min.js"></script> -->
<!-- <script src="<?= base_url() ?>/assets/libs/simplebar/dist/simplebar.js"></script> -->
<?php $uri = current_url(true); ?>

<script>
    $(document).ready(function () {
        getSiderBar()
    });
    
    function getSiderBar() {
        $.ajax({
            type: "get",
            url: "<?= base_url('Home/get_sidebar') ?>",
            dataType: "json",
            success: function (response) {
                var menu = JSON.parse(response.menu);
                menu.forEach(function (menu_id) {
                    // $("#" + menu_id + '_menu').show();
                });
            }
        });
    }
    // function checkSession() {
    //     $.ajax({
    //         url: '/isSession',
    //         method: 'GET',
    //         dataType: 'json',
    //         success: function(response) {
    //             if (response.status === 'session_expired') {
    //                 alert('Sesi telah habis. Anda akan diarahkan ke halaman login.');
    //                 window.location.href = "/login";
    //             }
    //         }
    //     });
    // }
    // setInterval(checkSession, 60000);
</script>



</body>

</html>