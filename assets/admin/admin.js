import './styles/admin.scss';

import 'bootstrap'
import 'admin-lte'

$('#confirm-delete').on('show.bs.modal', function(e) {
    $(this).find('.modal-body').text($(e.relatedTarget).data('name'));
    $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
});
