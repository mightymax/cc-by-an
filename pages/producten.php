<?php
$category = '';
if (!isset($sql)) {
    $categoryQuery = '';
    if (@$_REQUEST['category']) {
        $stmt = $dbh->prepare("SELECT id, name FROM property WHERE id=:id AND category='categorie' LIMIT 1"); 
        $stmt->bindParam(':id', $_REQUEST['category'], PDO::PARAM_INT);
        $stmt->execute(); 
        $category = $stmt->fetch();
        if ($category) {
            $categoryQuery = 'AND property.id=' . intval($category['id']);
        }
    }
    $sql = <<<SQL
SELECT product.*, property.name AS category 
FROM product 
JOIN product_has_property ON product.id=product_has_property.product
JOIN property ON property.id=product_has_property.property AND property.category='categorie'
WHERE 1 {$categoryQuery}
ORDER BY product.name
SQL;
}
?>
<?php if($category): ?>
<div class="container text-center">
    <h2><small class="text-muted"><?php echo $category['name'];?></small></h2>
</div>
<?php endif?>

<pre><?php echo $sql?></pre>
<div class="row row-cols-1 row-cols-md-3 mb-3 ">
    <?php foreach ($dbh->query($sql) as $row) :?>
    <div class="col">
        <div class="card mb-4 shadow-sm">
            <div class="card-header">
                <h4 class="my-0 fw-normal">
                    <?php echo $row['name']?>
                </h4>
            </div>
            <div class="card-body">
                <h1 class="card-title pricing-card-title">€ <?php echo intval($row['price']/100)?><small class="text-muted">,<?php echo str_pad(fmod($row['price'], 100), 2, '0')?></small></h1>
                <p><?php echo @$row['description']?></p>
                <ul class="list-unstyled mt-3 mb-4">
                </ul>
                <button type="button" class="w-100 btn btn-lg btn-outline-primary">
                    <i class="bi bi-basket"></i> In winkelwagen</button>
            </div>
        </div>
    </div>
    <?php endforeach?>
</div>
