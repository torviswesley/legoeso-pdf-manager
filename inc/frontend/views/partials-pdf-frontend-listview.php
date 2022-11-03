<div class="dataTables_wrapper dt-jqueryui">

    <div style="background: #f5f5f5; border-radius: 4px; padding: 1em; border: 1px solid #a3a3a3; font-size: 0.8rem;">
        <h3> List Results for Category: <?php echo (!empty($category)) ? strtoupper($category) : 'ALL Documents'; ?></h3>

    <table id="<?php echo esc_attr($pdm_datatable_id); ?>" class="display">
    </table>
    </div>
</div>