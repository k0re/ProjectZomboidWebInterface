<?php
require __DIR__ . '/vendor/autoload.php';
require ("functions.php");
if (!empty($_POST['wsid'])) {
    insert_wsitem(addslashes($_POST['wsid']));
}
if (!empty($_POST['action'])) {
    $data = !empty($_POST['data']) ? addslashes($_POST['data']) : "";
    switch ($_POST['action']) {
        case "delete":
            delete_wsitem($data);
            break;
        case "deactivate_ws":
            deactivate_item($data);
            break;
        case "activate_ws":
            activate_item($data);
            break;
        case "deactivate_mod":
            deactivate_module($data);
            break;
        case "activate_mod":
            activate_module($data);
            break;
        case "sync":
            sync_from_steam();
            break;
        case "start":
        case "stop":
        case "update":
        case "restart":
        case "updatewebif":
        case "save":
            runServerCommand($_POST['action']);
            break;
    }
}


?>


<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>m1 Zombieworld</title>


    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="vendor/fortawesome/font-awesome/free/css/all.min.css"/>
    <!-- Custom styles for this template -->
    <link href="css/cover.css" rel="stylesheet">
</head>

<body class="text-center">

<div class="cover-container d-flex h-100 p-3 mx-auto flex-column">
    <header class="masthead mb-auto" style="padding-bottom: 50px;">
        <div class="inner">
            <h3 class="masthead-brand" style="margin-left: -154px;margin-top: 10px;">Project Zomboid - <?php renderServerStatus(); ?> - <?php print_r(getCurrentPlayerCount()); ?> Players Online</h3>
        </div>
    </header>

    <main role="main" class="inner cover" style="width: 1024px; margin-left: -12rem">
        <h1 class="cover-heading">Mods</h1>
        <?php $mods = get_mods_from();?>
        <div class="container-fluid">
            <div class="row">
                <div class="col d-flex flex-column vh-100" style="max-height: 20em;">
                    <div class="row flex-grow-1 overflow-auto border">
                        <div class="col">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th scope="col" style="text-align: left; width: 120px;">Workshop ID</th>
                                    <th scope="col" style="text-align: left;">Mod Name</th>
                                    <th scope="col" style="text-align: left; width: 380px; ">Mod Modules</th>
                                    <th scope="col" style="text-align: left;">Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                function printTableList($list)
                                {
                                        foreach ($list as $id=>$module) {
                                            $module = (object) $module;
                                            if ($module->active) {
                                                echo "<span style='color: #198754; display: block'>".$module->name;
                                                echo ' <a href="#" onclick="ajaxCall(\'deactivate_mod\',\''.$id.'\')"><span class="fa-solid fa-lock" style="color: #3551dc"></span></a> ';
                                            }
                                            else {
                                                echo "<span style='color: #dc3545; display: block'>".$module->name;
                                                echo ' <a href="#" onclick="ajaxCall(\'activate_mod\',\''.$id.'\')"><span class="fa-solid fa-lock-open" style="color: #6ecb35"></span></a> ';
                                            }
                                            echo "</span>";
                                        }
                                }

                                foreach ($mods as $id=> $mod) {
                                    $mod = (object) $mod;
                                    if ($mod->active == 1) {
                                        $name = "<span style='color: #198754; display: block'>".$mod->name."</span>";
                                    }
                                    else {
                                        $name = "<span style='color: #dc3545; display: block'>".$mod->name."</span>";
                                    }
                                    echo "<tr><td style='text-align: left;'>".$id."</td><td style='text-align: left;'>".$name."</td><td style='text-align: left;'>";
                                    printTableList($mod->list);
                                    echo "</td><td style='text-align: left;'>";
                                    if ($mod->active == 1) {
                                        echo '<a href="#" onclick="ajaxCall(\'deactivate_ws\',\''.$id.'\')"><span class="fa-solid fa-lock" style="color: #3551dc"></span></a> ';
                                    }
                                    else {
                                        echo '<a href="#" onclick="ajaxCall(\'activate_ws\',\''.$id.'\')"><span class="fa-solid fa-lock-open" style="color: #6ecb35"></span></a> ';
                                    }
                                    echo '<a href="#" onclick="ajaxCall(\'delete\',\''.$id.'\')"><span class="fa-solid fa-trash" style="color: #dc3545"></span></a> ';
                                    echo '</td>';
                                    echo "</tr>";
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <main role="main" class="inner cover">
        <h1 class="cover-heading">Add Mod</h1>
        <form method="post" action="index.php">
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text" style="width: 120px;">Workshop ID</span>
                </div>
                <input type="number" class="form-control" name="wsid">
            </div>
            <input type="submit" class="btn btn-primary" value="Add Mod"></input>
        </form>
        <button type="button" class="btn btn-success" onclick='ajaxCall('sync','')" style="margin-top: 10px;">Sync From Steam</button>
    </main>
    <main role="main" class="inner cover">
        <h1 class="cover-heading">Control Server</h1>
        <button type="button" class="btn btn-success" onclick="ajaxCall('start','')">Start Server</button>
        <button type="button" class="btn btn-danger" onclick="ajaxCall('stop','')">Stop Server</button>
        <button type="button" class="btn btn-primary" onclick="ajaxCall('update','')">Update Server</button>
        <button type="button" class="btn btn-warning" onclick="ajaxCall('restart','')">Restart Server</button>
        <br>
        <br>
        <button type="button" class="btn btn-dark" onclick="ajaxCall('updatewebif','')">Update Webinterface</button>
        <button type="button" class="btn btn-secondary" onclick="ajaxCall('save','')">Save Map</button>
    </main>
    <footer class="mastfoot mt-auto">
        <div class="inner">
            <p></p>
        </div>
    </footer>
</div>
<div id="modaloverlay" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modaloverlay" style="padding-right: 17px; display: none;background-color: rgb(51, 51, 51);opacity: 0.8;">
    <span class="fas fa-sync fa-spin" style="font-size: 80px;margin-top: 300px;"></span>
</div>

<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="vendor/components/jquery/jquery.min.js"></script>
<script>
    window.jQuery || document.write('<script src="vendor/components/jquery/jquery.slim.min.js"><\/script>')
</script>
<script src="vendor/twbs/bootstrap/dist/js/bootstrap.min.js"></script>
<script>
    function ajaxCall(action, data) {
        $.post("index.php", {action: action, data: data})
            .done(function (data) {
                window.location.reload();
            });
    }
    $(document).ready(function() {
        $(document).ajaxStart(function () {
            $("#modaloverlay").fadeIn();
        });
    });
</script>
</body>

</html>

