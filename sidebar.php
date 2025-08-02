  
    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
      <!-- Brand Logo -->
      <a href="/Rayan_voip/index.php" class="brand-link">
        <img src="/Rayan_voip/dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
            style="opacity: .8">
        <span class="brand-text font-weight-light">پنل مدیریت</span>
      </a>

      <!-- Sidebar -->
      <div class="sidebar" style="direction: ltr">
        <div style="direction: rtl">
          <!-- Sidebar user panel (optional) -->
          <!-- <div class="user-panel mt-3 pb-3 mb-3 d-flex"> -->
            <!-- <div class="image">
              <img src="https://www.gravatar.com/avatar/?s=200&d=mp" 
                  alt="آواتار پیش‌فرض"
                  class="rounded-full w-20 h-20 shadow-md border border-gray-300">
            </div> -->
              <!-- <div class="info">
                <a href="#" class="d-block text-sm text-gray-700">کاربر : <strong><?php echo htmlspecialchars($_SESSION['user']['username']); ?></strong></a>
              </div> -->
          <!-- </div> -->

          <!-- Sidebar Menu -->
          <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
    <!-- مدیریت مرکز تماس -->
      <li class="nav-item has-treeview menu-open">
          <a href="/Rayan_voip/index.php" class="nav-link">
        <i class="nav-icon fa fa-phone-volume"></i> <!-- آیکن تماس -->
        <p>
          مدیریت مرکز تماس
          <i class="right fa fa-angle-left"></i>
        </p>
      </a>
      <ul class="nav nav-treeview menu-open">
        <!-- نظر سنجی در صف -->
       <li class="nav-item has-treeview menu-open">
          <a href="#" class="nav-link">
            <i class="nav-icon fa fa-poll"></i> <!-- آیکن نظرسنجی -->
            <p>
              نظر سنجی در صف
              <i class="fa fa-angle-left right"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
              <!-- داشبورد -->
              <li class="nav-item">
                  <a href="/Rayan_voip/Module/survey/main.php" class="nav-link">
                  <i class="fa fa-tachometer-alt nav-icon"></i> <!-- آیکن داشبورد -->
                  <p>داشبورد</p>
                  </a>
              </li>
              <?php
              if ($_SESSION['user']['role'] == 'admin') {
                ?>
              <!-- کاربران -->
              <li class="nav-item">
                  <a href="/Rayan_voip/Module/survey/users.php" class="nav-link">
                  <i class="fa fa-users nav-icon"></i> <!-- آیکن کاربران -->
                  <p>کاربران</p>
                  </a>
              </li>
              <?php
              }
              ?>
              <?php
              if ($_SESSION['user']['role'] == 'admin') {
                ?>
                <!-- تنظیمات -->
                <li class="nav-item">
                    <a href="/Rayan_voip/Module/survey/settings.php" class="nav-link">
                    <i class="fa fa-cogs nav-icon"></i> <!-- آیکن تنظیمات -->
                    <p>تنظیمات</p>
                    </a>
                </li>
                <?php
              }
              ?>
              
              <!-- نظرسنجی -->
              <li class="nav-item">
                  <a href="/Rayan_voip/Module/survey/survey.php" class="nav-link">
                  <i class="nav-icon fa fa-edit"></i> <!-- آیکن نظرسنجی -->
                  <p>نظرسنجی</p>
                  </a>
              </li>
              <!-- اپراتورها -->
              <li class="nav-item">
                  <a href="/Rayan_voip/Module/survey/operator_report.php" class="nav-link">
                  <i class="fa fa-headset nav-icon"></i> <!-- آیکن اپراتورها -->
                  <p>اپراتورها</p>
                  </a>
              </li>

              
              </ul>
          </li>
              
          </ul>
      </li>
            
      </ul>

          </nav>
          <!-- /.sidebar-menu -->
        </div>
      </div>
      <!-- /.sidebar -->
    </aside>
