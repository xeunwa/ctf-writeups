<?php

function sample_entries_exist($db) {
    // Check if at least one journal entry exists
    $count = $db->querySingle("SELECT COUNT(*) FROM journals");
    return ($count > 0);
}

function add_sample_entries() {
    $db = get_db_connection();
    
    // If any journal entries exist, do nothing
    if (sample_entries_exist($db)) {
        return;
    }
    
    // Create users if they don't already exist
    $stmt = $db->prepare("INSERT OR IGNORE INTO users (username, password) VALUES (:username, :password)");
    
    // Hashed password for both users
    $hashedPassword = '$2y$10$jN7cX.w0UqD826o2W0sX9OGCPGxnaTRMXivmPbUpEmHt1SVnu/MN.';
    
    // Insert Lyra Shadowfoot
    $stmt->bindValue(':username', 'Lyra Shadowfoot', SQLITE3_TEXT);
    $stmt->bindValue(':password', $hashedPassword, SQLITE3_TEXT);
    $stmt->execute();
    
    // Insert Garrick Stoneforge
    $stmt->bindValue(':username', 'Garrick Stoneforge', SQLITE3_TEXT);
    $stmt->bindValue(':password', $hashedPassword, SQLITE3_TEXT);
    $stmt->execute();
    
    // Retrieve the new user IDs
    $lyra_id = $db->querySingle("SELECT id FROM users WHERE username='Lyra Shadowfoot'");
    $garrick_id = $db->querySingle("SELECT id FROM users WHERE username='Garrick Stoneforge'");

    // Insert sample journal for Lyra
    $db->exec("
        INSERT INTO journals (user_id, title, content, is_public) VALUES 
        (
            $lyra_id, 
            'A Night of Cards at the Moonbeam Tavern', 
            'Tonight proved most profitable at the Moonbeam Tavern. The noble who challenged me to cards clearly underestimated a simple traveler''s wit. His coin purse now rests comfortably in my possession - a fair trade for the entertainment I provided, I''d say. The look on his face when I revealed my final hand was worth every copper piece.', 
            1
        )
    ");
    
    // Insert sample journal for Garrick
    $db->exec("
        INSERT INTO journals (user_id, title, content, is_public) VALUES 
        (
            $garrick_id, 
            'The Stone Lifting Challenge at Eastmarsh Festival', 
            'The festival at Eastmarsh today brought an unexpected challenge. The mighty stone that had defeated many strong men proved no match for my years of smithing work. The children''s faces lit up when I hoisted it overhead - though I must admit, my back might regret it come morning. Shared some tales of the forge with the young ones afterward.', 
            1
        )
    ");
}

// Run the function to add sample entries
add_sample_entries();
?>
