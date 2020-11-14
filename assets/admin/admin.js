import './styles/admin.scss';

import 'bootstrap'
import 'admin-lte'
import 'admin-lte/plugins/datatables/jquery.dataTables.min.js';
import 'admin-lte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js';
import 'admin-lte/plugins/datatables-responsive/js/dataTables.responsive.min.js';
import 'admin-lte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js';

$('#confirm-delete').on('show.bs.modal', function(e) {
    $(this).find('.modal-body').text($(e.relatedTarget).data('name'));
    $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
});

$(".data-grid").DataTable({
    "pageLength": 1000,
    "responsive": true,
    "autoWidth": false,
});