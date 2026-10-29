<aside
    class="admin-sidebar"
    id="adminSidebar"
    aria-label="Main navigation">
    <div class="sidebar-header">
        <a class="brand-mark" href="#" aria-label="">
            <span class="brand-icon"><i class="bi bi-chevron-double-left" aria-hidden="true"></i></span>
            <span class="brand-copy">
                <span class="brand-title">File Management</span>
                <span class="brand-subtitle">High Court of Chhattisgarh</span>
            </span>
        </a>
    </div>
    <?php
    $current_page = basename($_SERVER['PHP_SELF']);

    // Which pages belong to which group
    $entryPages  = ['file_master.php', 'add_docs.php', 'add_faq.php'];
    $reportPages = ['rpt_files.php', 'rpt_documents.php', 'rpt_faq.php'];

    $entryOpen  = in_array($current_page, $entryPages);
    $reportOpen = in_array($current_page, $reportPages);
    ?>
    <nav class="sidebar-nav" id="sidebarNav">

        <a class="nav-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
            <span class="nav-icon"><i class="bi bi-speedometer2" aria-hidden="true"></i></span>
            <span class="nav-text">Dashboard</span>
        </a>

        <!-- Entry group -->
        <a class="nav-link <?= $entryOpen ? 'active' : '' ?>" data-bs-toggle="collapse" href="#entryMenu"
            role="button" aria-expanded="<?= $entryOpen ? 'true' : 'false' ?>" aria-controls="entryMenu">
            <span class="nav-icon"><i class="bi bi-pencil-square" aria-hidden="true"></i></span>
            <span class="nav-text">Entry</span>
            <i class="bi bi-chevron-down ms-auto" aria-hidden="true"></i>
        </a>
        <div class="collapse <?= $entryOpen ? 'show' : '' ?>" id="entryMenu" data-bs-parent="#sidebarNav">
            <ul>
                <li>
                    <a class="nav-link sub-link <?= $current_page == 'file_master.php' ? 'active' : '' ?>" href="file_master.php">
                        <span class="nav-text">File Master</span>
                    </a>
                </li>
                <li>
                    <a class="nav-link sub-link <?= $current_page == 'add_docs.php' ? 'active' : '' ?>" href="add_docs.php">
                        <span class="nav-text">Add Documents</span>
                    </a>
                </li>
                <li>
                    <a class="nav-link sub-link <?= $current_page == 'add_faq.php' ? 'active' : '' ?>" href="add_faq.php">
                        <span class="nav-text">Add FAQs</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Reports group -->
        <a class="nav-link <?= $reportOpen ? 'active' : '' ?>" data-bs-toggle="collapse" href="#reportMenu"
            role="button" aria-expanded="<?= $reportOpen ? 'true' : 'false' ?>" aria-controls="reportMenu">
            <span class="nav-icon"><i class="bi bi-clipboard2-check" aria-hidden="true"></i></span>
            <span class="nav-text">Reports</span>
            <i class="bi bi-chevron-down ms-auto" aria-hidden="true"></i>
        </a>
        <div class="collapse <?= $reportOpen ? 'show' : '' ?>" id="reportMenu" data-bs-parent="#sidebarNav">
            <ul>
                <li>
                    <a class="nav-link sub-link <?= $current_page == 'rpt_files.php' ? 'active' : '' ?>" href="rpt_files.php">
                        <span class="nav-text">File Report</span>
                    </a>
                </li>
                <li>
                    <a class="nav-link sub-link <?= $current_page == 'rpt_documents.php' ? 'active' : '' ?>" href="rpt_documents.php">
                        <span class="nav-text">Document Report</span>
                    </a>
                </li>
                <li>
                    <a class="nav-link sub-link <?= $current_page == 'rpt_faq.php' ? 'active' : '' ?>" href="rpt_faq.php">
                        <span class="nav-text">FAQ Report</span>
                    </a>
                </li>


            </ul>
        </div>

        <!-- <a class="nav-link <?= $current_page == 'add_faq.php' ? 'active' : '' ?>" href="add_faq.php">
            <span class="nav-icon"><i class="bi bi-question-circle" aria-hidden="true"></i></span>
            <span class="nav-text">Add FAQ</span>
        </a> -->
        <!-- <a class="nav-link" href="#">
            <span class="nav-icon"><i class="bi bi-bar-chart-line" aria-hidden="true"></i></span>
            <span class="nav-text">Charts</span>
        </a>
        <a class="nav-link" href="#">
            <span class="nav-icon"><i class="bi bi-table" aria-hidden="true"></i></span>
            <span class="nav-text">Tables</span>
        </a>
        <a class="nav-link" href="#">
            <span class="nav-icon"><i class="bi bi-ui-checks-grid" aria-hidden="true"></i></span>
            <span class="nav-text">Forms</span>
        </a>
        <a class="nav-link" href="#">
            <span class="nav-icon"><i class="bi bi-grid-3x3-gap" aria-hidden="true"></i></span>
            <span class="nav-text">Components</span>
        </a>
        <a class="nav-link" href="#">
            <span class="nav-icon"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i></span>
            <span class="nav-text">Alerts</span>
        </a>
        <a class="nav-link" href="#">
            <span class="nav-icon"><i class="bi bi-window-stack" aria-hidden="true"></i></span>
            <span class="nav-text">Modals</span>
        </a>
        <a class="nav-link" href="#">
            <span class="nav-icon"><i class="bi bi-gear" aria-hidden="true"></i></span>
            <span class="nav-text">Settings</span>
        </a>
        <a class="nav-link" href="#">
            <span class="nav-icon"><i class="bi bi-file-earmark" aria-hidden="true"></i></span>
            <span class="nav-text">Blank Page</span>
        </a>  -->
    </nav>

    <!-- <div class="sidebar-user">
        <img class="avatar-img avatar-md sidebar-user-avatar" src="../assets/images/avatar/avatar.jpg" alt="">
        <strong></strong>
        <small>Active Workspace</small>
    </div> -->

    <div class="sidebar-footer">
        <span class="status-dot"></span>
        <span class="sidebar-footer-text">System running smoothly</span>
    </div>
</aside>