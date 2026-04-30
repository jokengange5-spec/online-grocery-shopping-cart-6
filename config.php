<?php

$host = "dpg-d7p9sfpkh4rs73da47t0-a";
$db_name = "online_shop_m071";
$username = "online_shop";
$password = "4n9SAIPuQoDJDY7IYxGdMUsPLwYUK18g";
$port = "5432";

try {

    $conn = new PDO(
        "pgsql:host=$host;port=$port;dbname=$db_name;sslmode=require",
        $username,
        $password
    );

    // Set error mode
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {

    die("Connection failed: " . $e->getMessage());

}

/* ==========================================
   MACHINE LEARNING: RECOMMENDATION ENGINE
   ========================================== */

/**
 * Mokuha og mga produkto nga sagad palit dungan sa usa ka specific item.
 * Base sa Association Rule Learning principle.
 */
function getRecommendations($current_product_name, $conn) {
    // 1. Kuhaon nato ang tanang orders diin naapil ang maong produkto
    $search = "%" . $current_product_name . "%";
    $stmt = $conn->prepare("SELECT total_products FROM orders WHERE total_products LIKE ?");
    $stmt->execute([$search]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $suggestions = [];

    foreach ($orders as $row) {
        // I-split ang string (pananglitan: "Beef ( 1 ), Talong ( 2 )")
        $products_in_order = explode(', ', $row['total_products']);
        
        foreach ($products_in_order as $item) {
            // Limpyohan ang ngalan (kuhaon ang parenthesis ug ang numero sa sulod)
            // Gigamit ang preg_replace para makuha lang ang product name
            $name = preg_replace('/\s\(\s\d+\s\)/', '', $item);
            
            // Kon dili kini ang produkto nga gitan-aw karon, i-count as suggestion
            if ($name != $current_product_name) {
                if (isset($suggestions[$name])) {
                    $suggestions[$name]++;
                } else {
                    $suggestions[$name] = 1;
                }
            }
        }
    }

    // I-sort base sa frequency (pinakadaghan nga dungan nga napalit)
    arsort($suggestions);
    
    // I-return ang top 3 nga produkto nga naay pinakataas nga "association"
    return array_slice(array_keys($suggestions), 0, 3);
}

?>
