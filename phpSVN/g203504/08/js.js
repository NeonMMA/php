function succsess(response) {
    $('#calendar').empty();
    $('#calendar').append(response);

}

function update() {
    var year = $('#year').val();
    var month = $('#month').val();
    var dat = {year : year, month : month};
    $.post("./index.php", dat, succsess, "html");
}