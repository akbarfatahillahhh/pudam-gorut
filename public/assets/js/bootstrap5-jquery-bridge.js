/*
 * Jembatan jQuery -> Bootstrap 5.
 *
 * Bootstrap 5 menghapus plugin jQuery ($.fn.modal, $.fn.dropdown, dst).
 * View-view di aplikasi ini masih memanggil $('#modal').modal('show') dan
 * $('#modal').modal('hide'), sedangkan markup-nya sudah Bootstrap 5
 * (data-bs-toggle / data-bs-dismiss). File ini memetakan panggilan gaya jQuery
 * ke bootstrap.Modal supaya kode lama tetap jalan tanpa mengubah 90+ pemanggilan.
 *
 * Wajib dimuat SETELAH bootstrap.bundle.min.js dan jQuery.
 */
(function ($) {
    if (!$ || !$.fn || !window.bootstrap) {
        return;
    }

    if (!$.fn.modal) {
        $.fn.modal = function (action) {
            return this.each(function () {
                var instance = bootstrap.Modal.getOrCreateInstance(this);

                switch (action) {
                    case 'show':
                        instance.show();
                        break;
                    case 'hide':
                        instance.hide();
                        break;
                    case 'toggle':
                        instance.toggle();
                        break;
                    case 'dispose':
                        instance.dispose();
                        break;
                }
            });
        };
    }

    if (!$.fn.dropdown) {
        $.fn.dropdown = function (action) {
            return this.each(function () {
                var instance = bootstrap.Dropdown.getOrCreateInstance(this);
                if (action === 'toggle') {
                    instance.toggle();
                } else if (action === 'show') {
                    instance.show();
                } else if (action === 'hide') {
                    instance.hide();
                }
            });
        };
    }

    if (!$.fn.collapse) {
        $.fn.collapse = function (action) {
            return this.each(function () {
                var instance = bootstrap.Collapse.getOrCreateInstance(this);
                if (action === 'toggle') {
                    instance.toggle();
                } else if (action === 'show') {
                    instance.show();
                } else if (action === 'hide') {
                    instance.hide();
                }
            });
        };
    }

    if (!$.fn.tab) {
        $.fn.tab = function (action) {
            return this.each(function () {
                bootstrap.Tab.getOrCreateInstance(this).show();
            });
        };
    }
})(window.jQuery);
