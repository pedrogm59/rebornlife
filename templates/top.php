<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="description" content="Arma 3 Life France - Administration">
    <meta name="theme-color" content="grey">
    <title>Administration - ArmaLifeFrance</title>
    <link rel="icon" sizes="192x192" href="/favicon.png">
    <link rel="apple-touch-icon-precomposed" href="/favicon.png"/>
    <link href="/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU"
          crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet"/>
    <?php if (isset($_COOKIE['darkMode']) and $_COOKIE['darkMode'] == 1) { ?>
        <link rel="stylesheet" href="/css/dark.min.css">
    <?php } else { ?>
        <link rel="stylesheet" href="/css/light.min.css">
    <?php } ?>
    <link rel="stylesheet" href="/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css"
          rel="stylesheet"/>
    <link rel="stylesheet" href="/css/noty.css">
    <link rel="stylesheet" href="/css/noty-bootstrap-v4.css">
    <link rel="stylesheet" href="/css/animate.css">
    <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49"
            crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js" integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em"
            crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
    <script src="/js/noty.min.js"></script>
</head>
<body>
<?php if (Auth::isLogged()) { ?>
    <nav id="menu" class="navbar navbar-expand-md navbar-dark navbar-custom sticky-top" style="background-color: #2a354f;">
        <a class="navbar-brand" href="/">
            <img src="/img/logo.png" width="50" height="30" class="d-inline-block align-top" alt="">
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                        <span class="fa fa-layer-group"></span> Services
                    </a>
                    <div class="dropdown-menu">
                        <h6 class="dropdown-header">Gendarmerie</h6>
                        <a class="dropdown-item" href="/Gendarmerie/Effectif.php"><span class="fa fa-users"></span> Effectif</a>
                        <a class="dropdown-item" href="/Gendarmerie/Garage.php"><span class="fa fa-warehouse"></span> Garage</a>
                        <a class="dropdown-item" href="/Gendarmerie/Service.php"><span class="fa fa-bell"></span> Prise de service</a>
                        <?php if (Auth::isModo()) { ?><a class="dropdown-item" href="/Gendarmerie/CNG.php"><span class="fa fa-euro-sign"></span> CNG</a><?php } ?>
                        <div class="dropdown-divider"></div>
                        <h6 class="dropdown-header">Sapeurs-Pompiers</h6>
                        <a class="dropdown-item" href="/SapeursPompiers/Effectif.php"><span class="fa fa-users"></span> Effectif</a>
                        <a class="dropdown-item" href="/SapeursPompiers/Garage.php"><span class="fa fa-warehouse"></span> Garage</a>
                        <a class="dropdown-item" href="/SapeursPompiers/Service.php"><span class="fa fa-bell"></span> Prise de service</a>
                        <?php if (Auth::isModo()) { ?><a class="dropdown-item" href="/SapeursPompiers/CNP.php"><span class="fa fa-euro-sign"></span> CNP</a><?php } ?>
                        <div class="dropdown-divider"></div>
                        <h6 class="dropdown-header">Administration Pénitentiaire</h6>
                        <a class="dropdown-item" href="/Prison/Effectif.php"><span class="fa fa-users"></span> Effectif</a>
                        <a class="dropdown-item" href="/Prison/Service.php"><span class="fa fa-bell"></span> Prise de service</a>
                        <div class="dropdown-divider"></div>
                        <h6 class="dropdown-header">Services Publics</h6>
                        <a class="dropdown-item" href="/ServicesPublics/Effectif.php"><span class="fa fa-users"></span> Effectif</a>
                        <a class="dropdown-item" href="/ServicesPublics/Service.php"><span class="fa fa-bell"></span> Prise de service</a>
                        <?php if (Auth::isModo()) { ?><a class="dropdown-item" href="/ServicesPublics/Entreprises.php"><span class="fa fa-building"></span> Entreprises</a><?php } ?>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                        <span class="fa fa-screwdriver"></span> Panel
                    </a>
                    <div class="dropdown-menu">
                        <?php if (Auth::isAdmin()) { ?>
                            <h6 class="dropdown-header">Panel</h6>
                            <a class="dropdown-item" href="/Admin/Panel/Logs.php"><span class="fa fa-list"></span> Logs</a>
                            <a class="dropdown-item" href="/Admin/Panel/Utilisateurs.php"><span class="fa fa-users-cog"></span> Utilisateurs</a>
                            <div class="dropdown-divider"></div>
                        <?php } ?>
                        <h6 class="dropdown-header">InGame</h6>
                        <?php if (Auth::isModo()) { ?>
                            <a class="dropdown-item" href="/Admin/Vehicules.php"><span class="fa fa-shopping-cart"></span> Shop Véhicules</a>
                            <a class="dropdown-item" href="/Admin/RechercheVehicule.php"><span class="fa fa-car"></span> Recherche Véhicules</a>
                            <a class="dropdown-item" href="/VisaAttente.php"><span class="fa fa-users-cog"></span> Visa en attente</a>
                            <?php if (Auth::isAdmin()) { ?>
                                <a class="dropdown-item" href="/Admin/Admins.php"><span class="fa fa-user-secret"></span> Admins</a>
                                <a class="dropdown-item" href="/Admin/LogsAdmin.php"><span class="fa fa-list-alt"></span> Logs Admin</a>
                            <?php }
                        } ?>
                        <a class="dropdown-item" href="/Admin/Logs.php"><span class="fa fa-list-alt"></span> Logs</a>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/Donateurs.php"><span class="fa fa-user-graduate"></span> Donateurs</a>
                </li>
                <?php if (Auth::isModo()) { ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                            <span class="fa fa-poll"></span> Podiums
                        </a>
                        <div class="dropdown-menu">
                            <h6 class="dropdown-header">Podiums</h6>
                            <a class="dropdown-item" href="/Podiums/Riches.php"><span class="fa fa-file-invoice-dollar"></span> Riches</a>
                            <a class="dropdown-item" href="/Podiums/Playtime.php"><span class="fa fa-hourglass"></span> Temps de jeu</a>
                        </div>
                    </li>
                <?php } ?>
                <?php if (Auth::hasRCon()) { ?>
                    <a class="nav-link" href="/Admin/RCon/Liste.php"><span class="fa fa-user-astronaut"></span> RCon</a>
                <?php } ?>
            </ul>
            <form class="form-inline mr-2" method="get" role="search" name="search" action="/Recherche.php">
                <div class="input-group input-group-sm">
                    <input type="search" class="form-control" name="q" placeholder="UID ou Nom" autocomplete="off"
                        <?php if (isset($_GET['q'])) { ?> value="<?= $_GET['q'] ?>" <?php } ?>>
                    <div class="input-group-append">
                        <button class="btn btn-outline-primary" type="submit"><span class="fa fa-search"></span></button>
                    </div>
                </div>
            </form>
            <div class="btn-group">
                <div class="dropdown">
                    <button class="btn btn-outline-light dropdown-toggle" type="button" data-toggle="dropdown">
                        <span class="fa fa-user-ninja"></span>
                        <?= $_SESSION['Auth']['username'] ?> - <?= Auth::getRole() ?>
                    </button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="/Profil.php">
                            <span class="fa fa-user-cog"></span> Profil</a>
                        <?php if (isset($_COOKIE['darkMode']) and $_COOKIE['darkMode'] == 1) { ?>
                            <btn class="dropdown-item" onclick='docCookies.removeItem("darkMode"); location.reload();'>
                                <i class="fas fa-sun"></i> Mode Jour
                            </btn>
                        <?php } else { ?>
                            <btn class="dropdown-item" onclick='docCookies.setItem("darkMode", "1"); location.reload();'>
                                <i class="fas fa-moon"></i> Mode Nuit
                            </btn>
                        <?php } ?>
                        <a class="dropdown-item" href="/logout.php">
                            <span class="fa fa-sign-out-alt"></span> Déconnexion</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>
<?php } ?>
<div class="container-fluid">
    <?php if (Auth::isLogged() and isset($_SESSION['alert'])) { ?>
        <script>
            new Noty({
                theme: 'bootstrap-v4',
                type: "<?= $_SESSION['alert']['type'] ?>",
                layout: 'topRight',
                timeout: 2000,
                text: "<?= $_SESSION['alert']['message'] ?>",
                animation: {
                    open: 'animated bounceInDown',
                    close: 'animated bounceOutDown',
                },
            }).show();
        </script>
        <?php unset($_SESSION['alert']);
    } ?>
    <section>