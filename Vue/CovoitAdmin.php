<?php include("Template/headerAdmin.php"); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Demande Admin</title>
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
        <h1>Tableau des demande</h1>
        <div class="table-responsive">
        <table class="table table-striped table-hover">

            <thead>
                <tr>
                    <th>id</th>
                    <th>Accepter</th>
                    <th>Trajet</th>
                    <th>User</th>
                    <th>Aller</th>
                    <th>Retour</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                require_once("../DAO/CovoitDAO.php");
                require_once("../Model/Covoit.php");
                $dbFile = '../DB/Donne.db';
                $pdo = new PDO('sqlite:' . $dbFile);
                $dao = new CovoitDAO($pdo);
                $Covoits = $dao->getAll();
                foreach ($Covoits as $Covoit) {
                    echo "<tr>";
                    echo "<td>" . $Covoit->getCovoitId() . "</td>";
                    echo "<td class='editable number'>" . $Covoit->getAccepter() . "</td>";
                    echo "<td class='editable number'>" . $Covoit->getTrajetId() . "</td>";
                    echo "<td class='editable number'>" . $Covoit->getUserId() . "</td>";
                    echo "<td class='editable number'>" . $Covoit->getAller() . "</td>";
                    echo "<td class='editable number'>" . $Covoit->getRetour() . "</td>";
                    
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
            <button id="add-trajet-button" class="btn btn-primary">Ajouter un Covoit</button>
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
                var Id = row.find('td:first').text();
                var Accepter,Trajet,User,Aller,Retour;
                rowData.each(function(index) {
                    var input = $(this).find('input');
                    var content = input.val();
                    if (input.attr('type') !== 'date' && content.trim() === '') {
                        isComplete = false;
                        input.addClass('incomplete');
                    } else {
                        input.removeClass('incomplete');
                        if (index === 0) {
                                Accepter = content;
                            } else if (index === 1) {
                                Trajet = content;
                            } else if (index === 2) {
                                User = content;
                            } else if (index === 3) {
                                Aller = content;
                            } else if (index === 4) {
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
                        accepter:Accepter,
                        trajet_id:Trajet,
                        user_id:User,
                        aller:Aller,
                        retour:Retour,
                        origine: "Covoit"

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
                    }})
                } else {
                    var toast = new bootstrap.Toast($('#notification-toast')[0]);
                    toast.show();
                    
                    row.removeClass('edit-mode');
                    row.find('.edit-button').show();
                    row.find('.delete-button').show();
                    row.find('.btn-info').show();
                    row.find('.actions').toggle();
                }
            });


            $(document).on('click', '.delete-button', function() {
                var row = $(this).closest('tr');
                var ID=row.find('td:first').text();
                $.ajax({
                    url: "../Controleur/modif.php",
                    contentType: "application/x-www-form-urlencoded",
                    type: "POST",
                    data: {
                        action: "supprimer",
                        id: ID,
                        origine: "Covoit"
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

                $('#add-trajet-button').on('click', function() {
                    var newRow = $('<tr></tr>');
                    newRow.addClass('edit-mode');
                    newRow.append('<td></td>');
                    newRow.append('<td class="editable number"><input type="number" class="form-control"></td>');
                    newRow.append('<td class="editable number"><input type="number" class="form-control"></td>');
                    newRow.append('<td class="editable number"><input type="number" class="form-control"></td>');
                    newRow.append('<td class="editable number"><input type="number" class="form-control"></td>');
                    newRow.append('<td class="editable number"><input type="number" class="form-control"></td>');
                    newRow.append("<td><button class='btn btn-primary edit-button'>Modifier</button><button class='btn btn-danger delete-button'>Supprimer</button><div class='actions' style='display: none;'>                                <button class='btn btn-success save-button'>Sauvegarder</button>                                <button class='btn btn-warning cancel-button'>Annuler</button>                            </div></td>");                    

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
