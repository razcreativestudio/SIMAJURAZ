<?php
$content = file_get_contents('C:\laragon\www\SIMAJURAZ\RAZknowledgebase.php');

$style_block = "
    <style>
        .kb-img-container {
            display: flex;
            gap: 20px;
            margin-top: 24px;
            margin-bottom: 32px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .kb-img-showcase {
            max-width: 100%;
            width: auto;
            max-height: 400px;
            object-fit: contain;
            border-radius: 12px;
            border: 1px solid var(--l-border);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .kb-img-showcase:hover {
            transform: scale(1.02);
        }
        @media (max-width: 768px) {
            .kb-img-showcase {
                max-height: 300px;
            }
        }
    </style>
</head>";

$content = str_replace('</head>', $style_block, $content);

$pattern1 = '/<div style="display:flex; gap:20px; margin-top:20px; overflow-x: auto; padding-bottom: 10px;">.*?<\/div>/s';
$replacement1 = '
                <div class="kb-img-container">
                    <img src="assets/images/ss_register.png" alt="Register" class="kb-img-showcase">
                    <img src="assets/images/ss_settings_top.png" alt="Settings" class="kb-img-showcase">
                </div>';
$content = preg_replace($pattern1, $replacement1, $content);

$pattern2 = '/<img src="assets\/images\/ss_inventory_modal.png" .*?>/s';
$replacement2 = '
                <div class="kb-img-container">
                    <img src="assets/images/ss_inventory_modal.png" alt="Inventory Modal" class="kb-img-showcase">
                </div>';
$content = preg_replace($pattern2, $replacement2, $content);

$pattern3 = '/<div style="display:flex; gap:20px; margin-top:20px;">\s*<img src="assets\/images\/ss_pos_payment.png".*?>\s*<img src="assets\/images\/ss_pos_receipt.png".*?>\s*<\/div>/s';
$replacement3 = '
                <div class="kb-img-container">
                    <img src="assets/images/ss_pos_payment.png" alt="Payment Modal" class="kb-img-showcase">
                    <img src="assets/images/ss_pos_receipt.png" alt="Receipt Modal" class="kb-img-showcase">
                </div>';
$content = preg_replace($pattern3, $replacement3, $content);

$pattern4 = '/<div style="display:flex; gap:20px; margin-top:20px;">\s*<img src="assets\/images\/ss_finance_in.png".*?>\s*<img src="assets\/images\/ss_finance_out.png".*?>\s*<\/div>/s';
$replacement4 = '
                <div class="kb-img-container">
                    <img src="assets/images/ss_finance_in.png" alt="Income Modal" class="kb-img-showcase">
                    <img src="assets/images/ss_finance_out.png" alt="Expense Modal" class="kb-img-showcase">
                </div>';
$content = preg_replace($pattern4, $replacement4, $content);

$pattern5 = '/<div style="display:flex; gap:20px; margin-top:20px;">\s*<img src="assets\/images\/ss_users_add.png".*?>\s*<img src="assets\/images\/ss_payroll_modal.png".*?>\s*<\/div>/s';
$replacement5 = '
                <div class="kb-img-container">
                    <img src="assets/images/ss_users_add.png" alt="Add Employee" class="kb-img-showcase">
                    <img src="assets/images/ss_payroll_modal.png" alt="Payroll Modal" class="kb-img-showcase">
                </div>';
$content = preg_replace($pattern5, $replacement5, $content);

$pattern6 = '/<img src="assets\/images\/ss_hpp.png" .*?>/s';
$replacement6 = '
                <div class="kb-img-container">
                    <img src="assets/images/ss_hpp.png" alt="HPP Calculator" class="kb-img-showcase">
                </div>';
$content = preg_replace($pattern6, $replacement6, $content);

$pattern7 = '/<img src="assets\/images\/ss_profitshare_tab.png" .*?>/s';
$replacement7 = '
                <div class="kb-img-container">
                    <img src="assets/images/ss_profitshare_tab.png" alt="Profit Share" class="kb-img-showcase">
                </div>';
$content = preg_replace($pattern7, $replacement7, $content);

$pattern8 = '/<img src="assets\/images\/ss_reports.png" .*?>/s';
$replacement8 = '
                <div class="kb-img-container">
                    <img src="assets/images/ss_reports.png" alt="Reports" class="kb-img-showcase">
                </div>';
$content = preg_replace($pattern8, $replacement8, $content);

file_put_contents('C:\laragon\www\SIMAJURAZ\RAZknowledgebase.php', $content);
file_put_contents('c:\Users\Administrator\Documents\DATA ZUL\RAZ\SIMAJURAZ\RAZknowledgebase.php', $content);
echo "Knowledgebase styling completely overhauled!";
?>
