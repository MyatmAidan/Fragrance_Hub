   <?php
    include __DIR__ . '/../../require/common.php';
    ?>
   <!DOCTYPE html>
   <html lang="en">

   <head>
       <meta charset="UTF-8" />
       <meta name="viewport" content="width=device-width, initial-scale=1.0" />
       <title>Admin Dashboard - iDukan</title>
       <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
       <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
       <link rel="stylesheet" href="../css/styles.css?v=<?= time() ?>" />
       <link rel="stylesheet" href="../css/adminstyle.css?v=<?= time() ?>" />
       <link rel="stylesheet" href="../node_modules/metismenujs/dist/metismenujs.css" />
       <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
       <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
       <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
   </head>
   <aside>
       <nav id="adminSidebar" class="sidebar">
           <div class="app-brand">
               <a href="" class="app-title">Fragrance_hub</a>
           </div>
           <ul class="sider_menu metismenu">
               <li>
                   <a class="has-arrow" aria-expanded="false">
                       <i class="bi bi-ui-checks icon">
                       </i>
                       <span>Fragrance_Brand</span>
                   </a>
                   <ul class="collapse-list">
                       <li id="c_list"><a href="<?= $admin_base_url ?>brand_list.php"><span>Brand_list</span></a></li>
                       <li id="c_list"><a href="<?= $admin_base_url ?>brand_create.php"><span>Brand_create</span></a></li>
                   </ul>
               </li>
               <li>
                   <a class="has-arrow" aria-expanded="false">
                       <i class="bi bi-ui-checks icon">
                       </i>
                       <span>Product</span>
                   </a>
                   <ul class="collapse-list">
                       <li id="c_list"><a href="<?= $admin_base_url ?>brand_list.php"><span>Product_list</span></a></li>
                       <li id="c_list"><a href="<?= $admin_base_url ?>brand_create.php"><span>Product_create</span></a></li>
                   </ul>
               </li>
           </ul>
       </nav>

   </aside>