$(document).ready(function () {
    $('#table').DataTable({
        order: [],
        "columnDefs": [
            { "orderData": [ 4, 2 ],    "targets": 4 },
            { "orderable": false, targets: 0 },
            { "orderable": false, targets: 3 },
            { "orderable": false, targets: 5 },
        ],
        "lengthMenu": [ [5, 10, 20, -1], [5, 10, 20, "All"] ],
        "language": {
            "lengthMenu": "Zobraziť _MENU_ záznamov na stránku",
            "info": "Zobrazujem _START_ - _END_ z celkovo _TOTAL_ záznamov",
            "search": "Hľadať:",
            "paginate": {
                "first":      "Prvá",
                "last":       "Posledná",
                "next":       "Ďalšia",
                "previous":   "Predošlá"
            },
        }
    });

    $(document).ready(function () {
        $('#tableBest').DataTable({
            order: [],
            "lengthMenu": [ [5, -1], [5, "All"] ],
            "language": {
                "lengthMenu": "Zobraziť _MENU_ záznamov na stránku",
                "info": "Zobrazujem _START_ - _END_ z celkovo _TOTAL_ záznamov",
                "search": "Hľadať:",
                "paginate": {
                    "first":      "Prvá",
                    "last":       "Posledná",
                    "next":       "Ďalšia",
                    "previous":   "Predošlá"
                },
            }
        });
    });
});

function checkLoginForm(){
    var login = document.getElementById("loginLogin").value;
    var password = document.getElementById("passwordLogin").value;
    if (validateLoginLogin(login)) {
        if (validatePasswordLogin(password)) {
            return true;
        }
    }
    return false;

}

function validateLoginLogin(login){
    var regex_email = /^(\w+([\. _]?\w+)*){3,20}@((\w+([\. _]?\w+)*)\.)+(\w{2,4})$/;
    var regex_login = /^\w{3,20}$/;
    if (regex_email.test(login) || regex_login.test(login)){
        document.getElementById("errorLogin").style.display = "none";
        return true;
    }else {
        document.getElementById("errorLogin").style.display = "block";
        return false;
    }
}

function validatePasswordLogin(password){
    var regex = /^[!-~]{8,64}$/;
    if (regex.test(password)){
        document.getElementById("errorPassword").style.display = "none";
        return true;
    }else {
        document.getElementById("errorPassword").style.display = "block";
        return false;
    }
}