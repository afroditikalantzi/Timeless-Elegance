<?php

// Get items per page setting from database
function get_items_per_page($conn)
{
    $sql = "SELECT setting_value FROM settings WHERE setting_name = 'items_per_page'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return (int)$row['setting_value'];
    } else {
        return 10; 
    }
}

// Generate pagination links
function generate_pagination($current_page, $total_pages, $base_url)
{
    $html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';

    // Previous button
    $prev_disabled = ($current_page <= 1) ? 'disabled' : '';
    $prev_url = ($current_page > 1) ? $base_url . '&page=' . ($current_page - 1) : '#';
    $html .= '<li class="page-item ' . $prev_disabled . '"><a class="page-link" href="' . $prev_url . '">&laquo; Previous</a></li>';

    // Page numbers
    $start = max(1, $current_page - 2);
    $end = min($total_pages, $current_page + 2);

    for ($i = $start; $i <= $end; $i++) {
        $active = ($i == $current_page) ? 'active' : '';
        $html .= '<li class="page-item ' . $active . '"><a class="page-link" href="' . $base_url . '&page=' . $i . '">' . $i . '</a></li>';
    }

    // Next button
    $next_disabled = ($current_page >= $total_pages) ? 'disabled' : '';
    $next_url = ($current_page < $total_pages) ? $base_url . '&page=' . ($current_page + 1) : '#';
    $html .= '<li class="page-item ' . $next_disabled . '"><a class="page-link" href="' . $next_url . '">Next &raquo;</a></li>';

    $html .= '</ul></nav>';
    return $html;
}

// Sanitize input data
function sanitize_input($conn, $input)
{
    return mysqli_real_escape_string($conn, trim($input));
}

