<?php // File: partials/footer.php ?>

    <?php // Page specific content ends before this ?>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> <?php // THIS IS VERY IMPORTANT! ?>
    
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>


    <?php if (isset($page_specific_js)) { echo $page_specific_js; } // For page-specific scripts ?>

</body>
</html>