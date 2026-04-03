<?php
include 'connect.php';

$search = $_POST['search'] ?? '';

if (empty($search)) {
    echo "<p>Please type something to search.</p>";
    exit();
}

echo "<h3>Search Results for: <em>".htmlspecialchars($search)."</em></h3>";

function showResults($result, $title, $formatter) {
    if ($result && $result->num_rows > 0) {
        echo "<h4>$title</h4><ul>";
        while($row = $result->fetch_assoc()) {
            echo "<li>".$formatter($row)."</li>";
        }
        echo "</ul>";
    }
}

// Books
$books = $conn->query("SELECT BookTitle, Quantity FROM books WHERE BookTitle LIKE '%$search%'");
showResults($books, "📚 Books", fn($r) => $r['BookTitle']." (Qty: ".$r['Quantity'].")");

// Authors
$authors = $conn->query("SELECT AuthorName FROM authors WHERE AuthorName LIKE '%$search%'");
showResults($authors, "✍️ Authors", fn($r) => $r['AuthorName']);

// Users
$users = $conn->query("SELECT Name, Email FROM user WHERE Name LIKE '%$search%' OR Email LIKE '%$search%'");
showResults($users, "👥 Users", fn($r) => $r['Name']." (".$r['Email'].")");

// Borrows
$borrows = $conn->query("SELECT BorrowID, Status FROM borrow WHERE BorrowID LIKE '%$search%' OR Status LIKE '%$search%'");
showResults($borrows, "📝 Borrow Records", fn($r) => "Borrow ID ".$r['BorrowID']." - ".$r['Status']);

// Notifications
$notices = $conn->query("SELECT Message, Status FROM notifications WHERE Message LIKE '%$search%' OR Status LIKE '%$search%'");
showResults($notices, "📣 Notifications", fn($r) => $r['Message']." (".$r['Status'].")");

// Fines
$fines = $conn->query("SELECT Type, Status, Amount FROM fines WHERE Type LIKE '%$search%' OR Status LIKE '%$search%'");
showResults($fines, "💰 Fines", fn($r) => $r['Type']." - ".$r['Status']." (₱".$r['Amount'].")");

// Materials
$materials = $conn->query("SELECT MaterialName, CategoryName FROM materials WHERE MaterialName LIKE '%$search%' OR CategoryName LIKE '%$search%'");
showResults($materials, "🧰 Materials", fn($r) => $r['MaterialName']." (".$r['CategoryName'].")");

// Archive
$archive = $conn->query("SELECT BookTitle, DeletedAt FROM book_archive WHERE BookTitle LIKE '%$search%'");
showResults($archive, "📂 Archive", fn($r) => $r['BookTitle']." (Deleted: ".$r['DeletedAt'].")");

// Publisher
$publisher = $conn->query("SELECT PublisherName FROM publisher WHERE PublisherName LIKE '%$search%'");
showResults($publisher, "🏢 Publisher", fn($r) => $r['PublisherName']);

// Categories
$categories = $conn->query("SELECT CategoryName FROM categories WHERE CategoryName LIKE '%$search%'");
showResults($categories, "📖 Categories", fn($r) => $r['CategoryName']);

?>
