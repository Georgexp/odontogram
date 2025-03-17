<?php
// Load the OpenEMR environment
require_once("../../globals.php");
require_once("$srcdir/forms.inc.php");

// Retrieve essential parameters
$encounter = $_SESSION['encounter'] ?? 0;
$pid = $_SESSION['pid'] ?? 0;
$form_id = $_GET['id'] ?? 0; // Form ID in the `forms` table

// Validate parameters
if (!$encounter || !$pid || !$form_id) {
    die(xl("Falta el encuentro, paciente o ID del formulario"));
}

// Query the odontogram interventions
$interventions = [];
$result = sqlStatement(
    "SELECT h.*, o.svg_id 
     FROM form_odontogram_history h
     LEFT JOIN form_odontogram o ON h.odontogram_id = o.id
     WHERE h.pid = ? AND h.encounter = ?",
    [$pid, $encounter]
);
while ($row = sqlFetchArray($result)) {
    $interventions[] = $row;
}
?>

<html>
<head>
    <title><?php echo xlt('Odontograma'); ?></title>
    <link rel="stylesheet" href="<?php echo $GLOBALS['webroot']; ?>/public/assets/bootstrap-5.3.0-dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h2><?php echo xlt('Odontograma'); ?></h2>
        <!-- Container for the odontogram SVG -->
        <div id="odontogram-svg" style="width: 1048px; height: 704px;"></div>

         <!-- Interventions table -->
        <h3><?php echo xlt('Interventions'); ?></h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><?php echo xlt('Tooth'); ?></th>
                    <th><?php echo xlt('Type of Intervention'); ?></th>
                    <th><?php echo xlt('Option'); ?></th>
                    <th><?php echo xlt('Symbol'); ?></th>
                    <th><?php echo xlt('Code'); ?></th>
                    <th><?php echo xlt('Date'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($interventions as $intervention) { ?>
                    <tr>
                        <td><?php echo text($intervention['svg_id']); ?></td>
                        <td><?php echo text($intervention['intervention_type']); ?></td>
                        <td><?php echo text($intervention['option_id']); ?></td>
                        <td><?php echo text($intervention['symbol']); ?></td>
                        <td><?php echo text($intervention['code']); ?></td>
                        <td><?php echo text($intervention['date']); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

     <!-- Scripts to load and manipulate the SVG -->
    <script src="<?php echo $GLOBALS['webroot']; ?>/public/assets/jquery-3.6.0/jquery.min.js"></script>
    <script src="<?php echo $GLOBALS['webroot']; ?>/public/assets/svg.js/svg.min.js"></script>
    <script>
        $(document).ready(function() {
           // Create the SVG canvas
            var draw = SVG().addTo('#odontogram-svg').size(1048, 704);

            // Load the base SVG for the odontogram
            $.get('/interface/forms/odontogram/assets/odontogram.svg', function(svgData) {
                draw.svg(svgData);

                // Overlay intervention symbols
                <?php foreach ($interventions as $intervention) { ?>
                    draw.image('/interface/forms/odontogram/php/get_symbol.php?symbol=<?php echo urlencode($intervention['symbol']); ?>')
                        .size(30, 30)
                        .move($('#<?php echo js_escape($intervention['svg_id']); ?>').attr('x'), $('#<?php echo js_escape($intervention['svg_id']); ?>').attr('y'));
                <?php } ?>
            }, 'text');
        });
    </script>
</body>
</html>
