<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>siderbar</title>
    <link rel="stylesheet" href="../../css/adminstyle.css?v=<?= time(); ?>" />
    <link rel="stylesheet" href="../../node_modules/metismenujs/dist/metismenujs.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">



</head>

<body>
    <aside>
        <nav id="adminSidebar" class="sidebar">
            <div class="app-brand">
                <a href="" class="app-title">Fragrance_hub</a>
            </div>
            <ul class="sider_menu metismenu">
                <li class="mm-active">
                    <a class="has-arrow" aria-expanded="true">
                        <i class="bi bi-ui-checks icon">
                        </i>
                        <span>Categories</span>
                    </a>
                    <ul class="collapse-list mm-show">
                        <li id="c_list"><a href=""><span>Category_list</span></a></li>
                        <li id="c_list"><a href=""><span>Category_create</span></a></li>
                    </ul>
                </li>
            </ul>
        </nav>

    </aside>
    <script src="../../node_modules/metismenujs/dist/metismenujs.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new MetisMenu('.metismenu');
        });
    </script>

</body>

</html>