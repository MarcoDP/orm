<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <title>MDP - ORM</title>
        
        <!--Favicon-->
        <link rel="icon" href="./img/favicon.ico" />

        <!-- Bootstrap -->
        <link href="vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    </head>
    <body>
        
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container">
                <a class="navbar-brand" href="#">MDP @ ORM</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
                </button>
                <span class="navbar-text">
                    v 0.0.1
                </span>
            </div>
        </nav>
        
        <div class="container">
 
            <div class="row mt-3">
                <div class="col-4">
                    <div class="card">
                        <div class="card-body">
                            <form id="formDatabase">
                                <div class="input-group mb-3">
                                    <span class="input-group-text">Host</span>
                                    <input type="text" name="host" class="form-control" id="host">
                                    <span class="input-group-text">:</span>
                                    <input type="text" name="port" class="form-control" id="port">
                                    <span class="input-group-text">Port</span>
                                </div>
                                <div class="input-group mb-3">
                                    <span class="input-group-text">DataBase</span>
                                    <input type="text" name="db" class="form-control" id="db">
                                </div>
                                <div class="input-group mb-3">
                                    <span class="input-group-text">Username</span>
                                    <input type="text" name="user" class="form-control" id="user">
                                </div>
                                <div class="input-group mb-3">
                                <span class="input-group-text">Password</span>
                                    <input type="text" name="pass" class="form-control" id="pass">
                                </div>
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-primary float-end" id="btnInterrogaDatabase">Interroga Database</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-8">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th class="text-center">
                                    <input type="checkbox" id="chkSelectAll">
                                </th>
                                <th>Nome Tabella</th>
                                <th>Nome Classe</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="3">Interroga il Database per cui vuoi creare le classi.</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end">
                                    <button type="button" id="btnGeneraClassi" class="btn btn-primary" disabled>Genera classi</button>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                    <h5>
                        Le classi sono state create nella directory <em>classes/</em> con i seguenti nomi file:
                    </h5>
                    <ul>
                        <!-- AJAX DATA -->
                    </ul>
                </div>
            </div>
        </div>
        
        <script src="vendor/twbs/bootstrap/dist/js/bootstrap.min.js" type="text/javascript"></script>
        <script src="vendor/components/jquery/jquery.min.js" type="text/javascript"></script>
        <script src="js/script.js" type="text/javascript"></script>
        
    </body>

    <div class="position-fixed top-50 start-50 translate-middle" style="z-index: 11">
        <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true">
            <div class="toast-header bg-danger">
                <strong class="me-auto text-white">Connessione Fallita</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                Hello, world! This is a toast message.
            </div>
        </div>
    </div>

</html>
