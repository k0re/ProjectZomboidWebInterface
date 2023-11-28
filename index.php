<?php
require __DIR__ . '/vendor/autoload.php';
require ("functions.php");

if (!empty($_POST['wsid'])) {
    insert_wsitem(addslashes($_POST['wsid']));
}

if (!empty($_GET['delete'])) {
    try {
        delete_wsitem(addslashes($_GET['delete']));
    } catch (\Jelix\IniFile\IniException $e) {
    }
}
if (!empty($_GET['deactivate_ws'])) {
    try {
        deactivate_item(addslashes($_GET['deactivate_ws']));
    } catch (\Jelix\IniFile\IniException $e) {
    }
}

if (!empty($_GET['activate_ws'])) {
    try {
        activate_item(addslashes($_GET['activate_ws']));
    } catch (\Jelix\IniFile\IniException $e) {
    }
}

if (!empty($_GET['deactivate_mod'])) {
    try {
        deactivate_module(addslashes($_GET['deactivate_mod']));
    } catch (\Jelix\IniFile\IniException $e) {
    }
}

if (!empty($_GET['activate_mod'])) {
    try {
        activate_module(addslashes($_GET['activate_mod']));
    } catch (\Jelix\IniFile\IniException $e) {
    }
}

if (!empty($_GET['action'])) {
    runServerCommand(addslashes($_GET['action']));
}

if (!empty($_GET['sync'])) {
    try {
        sync_from_steam();
    } catch (Exception $e) {

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
    <header class="masthead mb-auto">
        <div class="inner">
            <h3 class="masthead-brand">Project Zomboid</h3>
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
                                                echo ' <a href="index.php?deactivate_mod=' . $id . '"><span class="fa-solid fa-lock" style="color: #3551dc"></span></a> ';
                                            }
                                            else {
                                                echo "<span style='color: #dc3545; display: block'>".$module->name;
                                                echo ' <a href="index.php?activate_mod=' . $id . '"><span class="fa-solid fa-lock-open" style="color: #6ecb35"></span></a> ';
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
                                    echo "<tr><td style='text-align: left;'>".$id."</td><td style='text-align: left;'>".$name."</td><td>";
                                    printTableList($mod->list);
                                    echo "</td><td style='text-align: left;'>";
                                    if ($mod->active == 1) {
                                        echo '<a href="index.php?deactivate_ws=' . $id . '"><span class="fa-solid fa-lock" style="color: #3551dc"></span></a> ';
                                    }
                                    else {
                                        echo '<a href="index.php?activate_ws=' . $id . '"><span class="fa-solid fa-lock-open" style="color: #6ecb35"></span></a> ';
                                    }
                                    echo '<a href="index.php?delete='.$id.'"><span class="fa-solid fa-trash" style="color: #dc3545"></span></a> ';
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
        <button type="button" class="btn btn-success" onclick="syncfromSteam()">Sync From Steam</button>
    </main>
    <main role="main" class="inner cover">
        <h1 class="cover-heading">Control Server</h1>
        <button type="button" class="btn btn-success" onclick="action('start')">Start Server</button>
        <button type="button" class="btn btn-danger" onclick="action('stop')">Stop Server</button>
        <button type="button" class="btn btn-primary" onclick="action('update')">Update Server</button>
        <button type="button" class="btn btn-warning" onclick="action('restart')">Restart Server</button>
    </main>
    <footer class="mastfoot mt-auto">
        <div class="inner">
            <p></p>
        </div>
    </footer>
</div>


<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script>
    window.jQuery || document.write('<script src="../../assets/js/vendor/jquery-slim.min.js"><\/script>')
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.9.2/umd/popper.min.js" integrity="sha512-2rNj2KJ+D8s1ceNasTIex6z4HWyOnEYLVC3FigGOmyQCZc2eBXKgOxQmo3oKLHyfcj53uz4QMsRCWNbLd32Q1g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
<script>
    function action(name) {
        window.location.href = "index.php?action=" + name;
    }
    function syncfromSteam() {
        window.location.href = "index.php?sync=true";
    }
</script>
</body>

</html>

