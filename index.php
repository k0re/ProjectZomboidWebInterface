<?php
require __DIR__ . '/vendor/autoload.php';
require ("functions.php");
print_r($_POST);
print_r($_GET);
if (!empty($_POST['wsid'])) {
    insert_wsitem(addslashes($_POST['wsid']));
}
if (!empty($_POST['action'])) {
    $data = !empty($_POST['data']) ? addslashes($_POST['data']) : "";
    echo "\n\n\n\n\n\n\n\n\n".$_POST['action'];
    echo "\n\n\n\n\n\n\n\n\n".$data;
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
        runServerCommand($data);
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
    <link rel="icon" href="/docs/4.0/assets/img/favicons/favicon.ico">

    <title>m1 Zombieworld</title>


    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
                                    <th scope="col" style="text-align: left;">Workshop ID</th>
                                    <th scope="col" style="text-align: left;">Mod Name</th>
                                    <th scope="col" style="width: 380px; text-align: left;">Mod Modules</th>
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
</div>

<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script>
    window.jQuery || document.write('<script src="../../assets/js/vendor/jquery-slim.min.js"><\/script>')
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.9.2/umd/popper.min.js" integrity="sha512-2rNj2KJ+D8s1ceNasTIex6z4HWyOnEYLVC3FigGOmyQCZc2eBXKgOxQmo3oKLHyfcj53uz4QMsRCWNbLd32Q1g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
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
    }
</script>
</body>

</html>

