function preview() {
    $('#prefixe').keyup(function (e) {
        $('#previewP').text($(this).val());
        $('#previewL').text("_".repeat($('#previewL').val()-$('#suffixe').val().length-$('#prefixe').val().length));
    });
    $('#suffixe').keyup(function (e) {
        $('#previewS').text($(this).val());
        $('#previewL').text("_".repeat($('#previewL').val()-$('#suffixe').val().length-$('#prefixe').val().length));
    });
    $('#longueur').change(function (e) {
        $('#previewL').text("_".repeat($('#previewL').val()-$('#suffixe').val().length-$('#prefixe').val().length));
    });
    $('#ext').keyup(function (e) {
        $('#previewE').text($(this).val());
        $('#previewL').text("_".repeat($('#previewL').val()-$('#suffixe').val().length-$('#prefixe').val().length));
    });
}

$(function () {
    preview();
});