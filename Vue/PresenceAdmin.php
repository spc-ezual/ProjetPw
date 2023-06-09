<?php include("Template/headerAdmin.php"); ?>
<!DOCTYPE html>
<html>
<head>
    <title>presence Admin</title>
    <link href="Css/bootstrap.css" rel="stylesheet">
    <link href="Css/style.css" rel="stylesheet">

</head>
<body>
    <div id="notification-toast" class="toast floating-toast bg-warning text-light" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false">
        <div class="toast-header">
            <strong class="me-auto">Attention</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            Veuillez remplir tous les champs avant de sauvegarder.
        </div>
    </div>
    <div class="container">
        <h1>Tableau des presences</h1>
        <div class="table-responsive">
        <table class="table table-striped table-hover">

            <thead>
                <tr>
                    <th>Id</th>
                    <th>User</th>
                    <th>Festival</th>
                    <th>Date d'aller</th>
                    <th>Date de retour</th>
                    <th>Aller</th>
                    <th>Retour</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                require_once("../DAO/presenceDAO.php");
                require_once("../Model/presence.php");
                $dbFile = '../DB/Donne.db';
                $pdo = new PDO('sqlite:' . $dbFile);
                $dao = new presenceDAO($pdo);
                $presences = $dao->getAll();
                foreach ($presences as $presence) {
                    echo "<tr>";
                    echo "<td>" . $presence->getId() . "</td>";
                    echo "<td class='editable number'>" . $presence->getUserId() . "</td>";
                    echo "<td class='editable number'>" . $presence->getFestivalId() . "</td>";
                    echo "<td class='editable date'>" . $presence->getDateAller() . "</td>";
                    echo "<td class='editable date'>" . $presence->getDateRetour() . "</td>";
                    echo "<td class='editable text'>" . $presence->getAller() . "</td>";
                    echo "<td class='editable text'>" . $presence->getRetour() . "</td>";
                    echo "<td>
                            <button class='btn btn-primary edit-button'>Modifier</button>
                            <button class='btn btn-danger delete-button'>Supprimer</button>
                            <div class='actions' style='display: none;'>
                                <button class='btn btn-success save-button'>Sauvegarder</button>
                                <button class='btn btn-warning cancel-button'>Annuler</button>
                            </div>
                        </td>";
                    echo "</tr>";
                }
                $pdo = null;
                ?>
                
            </tbody>
        </table>
        </div>
        <div class="text-center mt-4">
            <button id="add-presence-button" class="btn btn-primary">Ajouter un presence</button>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.edit-button').on('click', function() {
                var row = $(this).closest('tr');
                var rowData = row.find('.editable');

                var rowtext=row.find('.text')
                var rowDate=row.find('.date')
                var rowNumber=row.find('.number')
                rowtext.each(function() {
                    var content = $(this).text();
                    $(this).html('<input type="text" class="form-control" value="' + content + '">');
                    $(this).find('input').data('original-value', content); // Enregistrer la valeur d'origine dans l'attribut de données

                });
                rowDate.each(function() {
                    var content = $(this).text();
                    $(this).html('<input type="date" class="form-control" value="' + content + '">');
                    $(this).find('input').data('original-value', content); // Enregistrer la valeur d'origine dans l'attribut de données

                });
                rowNumber.each(function() {
                    var content = $(this).text();
                    $(this).html('<input type="number" class="form-control" value="' + content + '">');
                    $(this).find('input').data('original-value', content); // Enregistrer la valeur d'origine dans l'attribut de données

                });
                row.addClass('edit-mode');
                $(this).hide();
                row.find('.delete-button').hide();
                row.find('.actions').toggle();
            });

            $(document).on('click', '.cancel-button', function() {
                var row = $(this).closest('tr');
                Id = row.find('td:first').text();
                if(Id.trim()==='')row.remove();
                else {var rowData = row.find('.editable');
                rowData.each(function() {
                var originalValue = $(this).find('input').data('original-value'); // Récupérer la valeur d'origine à partir de l'attribut de données
                $(this).html(originalValue);
                    });
                    row.removeClass('edit-mode');
                    row.find('.edit-button').show();
                    row.find('.delete-button').show();
                    row.find('.actions').toggle();
                }});

            $(document).on('click', '.save-button', function() {
                var row = $(this).closest('tr');
                var rowData = row.find('.editable');
                var isComplete = true;
                var Id, User,Festival, Date_aller, Date_retour, Aller, Retour;

                // Obtenir la valeur de la colonne ID (colonne 0)
                Id = row.find('td:first').text();

                // Parcourir les autres colonnes éditables
                rowData.each(function(index) {
                        var input = $(this).find('input');
                        var content = input.val();
                        if (content.trim() === '') {
                            isComplete = false;
                            input.addClass('incomplete');
                        } else {
                            input.removeClass('incomplete');
                            //console.log(content);
                            if (index === 0) {
                                User = content;
                            } else if (index === 1) {
                                Festival = content;
                            } else if (index === 2) {
                                Date_aller = content;
                            } else if (index === 3) {
                                Date_retour = content;
                            } else if (index === 4) {
                                Aller = content;
                            } else if (index === 5) {
                                Retour = content;
                            }
                        }
                });
                if (isComplete) {
                    var Action=""
                    if (Id.trim() === '') {
                        // La variable id est vide
                        Action="ajouter"
                    } else {
                        // La variable id n'est pas vide
                        Action="modifier"
                    }

                    $.ajax({
                    url: "../Controleur/modif.php",
                    type: "POST",
                    contentType: "application/x-www-form-urlencoded",
                    data: {
                        action: Action,
                        id:Id,
                        festivalier_id:User,
                        festival_id:Festival,
                        date_aller:Date_aller,
                        date_retour:Date_retour,
                        aller:Aller,
                        retour:Retour,
                        origine: "Presence"

                    },

                    success: function(response) {
                        rowData.each(function() {
                        var content = $(this).find('input').val();
                        $(this).html(content);
                            });
                            row.removeClass('edit-mode');
                            row.find('.edit-button').show();
                            row.find('.delete-button').show();
                            row.find('.btn-info').show();
                            row.find('.actions').toggle();
                            if(Action==='ajouter') {
                            //modifie la valeur de la 1ere colone par message.id 
                            var idColumn = row.find('td:first');
                            
                            idColumn.text(response.id);

                            }
                            },
                    error: function(response) {
                        console.log('erreur '+response.message);
                    },
                    complete: function(response) {
                        console.log("Complete");
                    }
                });

                
            } else {
                var toast = new bootstrap.Toast($('#notification-toast')[0]);
                toast.show();
            }
        });


    $(document).on('click', '.delete-button', function() {
            var row = $(this).closest('tr');
            var ID = row.find('td:first').text();

            $.ajax({
                url: "../Controleur/modif.php",
                contentType: "application/x-www-form-urlencoded",
                type: "POST",
                data: {
                    action: "supprimer",
                    id: ID,
                    origine: "Presence"
                },
                success: function(response) {
                    // Supprimer la ligne si nécessaire
                    row.remove();
                },
                error: function(response) {
                    console.log("erreur");
                },
                complete: function(response) {
                    console.log("Complete");
                }
            });
        });
                $('#add-presence-button').on('click', function() {
                    var newRow = $('<tr></tr>');
                    newRow.addClass('edit-mode');
                    newRow.append('<td></td>');
                    newRow.append('<td class="editable"><input class="form-control" type="number"></td>');
                    newRow.append('<td class="editable"><input class="form-control" type="number"></td>');
                    newRow.append('<td class="editable"><input class="form-control" type="date"></td>');
                    newRow.append('<td class="editable"><input class="form-control" type="date"></td>');
                    newRow.append('<td class="editable"><input class="form-control" type="number"></td>');
                    newRow.append('<td class="editable"><input class="form-control" type="number"></td>');
                    newRow.append("<td><button class='btn btn-primary edi   t-button'>Modifier</button><button class='btn btn-danger delete-button'>Supprimer</button><div class='actions' style='display: none;'>                                <button class='btn btn-success save-button'>Sauvegarder</button>                                <button class='btn btn-warning cancel-button'>Annuler</button>                            </div></td>");                    

                    // Afficher les boutons "Sauvegarder" et "Annuler" et masquer les boutons "Modifier" et "Supprimer"
                    newRow.find('.save-button, .cancel-button').show();
                    newRow.find('.edit-button, .delete-button').hide();
                    newRow.find('.actions').toggle();
                    $('tbody').append(newRow);
        });



        });
    </script>
</body>
</html>
