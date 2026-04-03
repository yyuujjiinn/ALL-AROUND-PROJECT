<?php
include 'connect.php';

$search = $_POST['search'] ?? '';

if (empty($search)) {
    exit(); // no "please type something" message, para clean dropdown
}

function showResults($result, $title, $formatter) {
    if ($result && $result->num_rows > 0) {
        echo "<h4 style='margin:8px 0;'>$title</h4>";
        while($row = $result->fetch_assoc()) {
            echo $formatter($row);
        }
    }
}

// Books → link to book.php
$books = $conn->query("SELECT BookTitle, Quantity FROM books WHERE BookTitle LIKE '%$search%'");
showResults($books, "📚 Books", fn($r) =>
    "<div style='padding:8px;'>
        <a href='book.php?title=".urlencode($r['BookTitle'])."' style='text-decoration:none; color:#333;'>
            ".$r['BookTitle']." (Qty: ".$r['Quantity'].")
        </a>
    </div>"
);

// Authors → link to book.php filtered by author
$authors = $conn->query("SELECT AuthorName FROM authors WHERE AuthorName LIKE '%$search%'");
showResults($authors, "✍️ Authors", fn($r) =>
    "<div style='padding:8px;'>
        <a href='book.php?author=".urlencode($r['AuthorName'])."' style='text-decoration:none; color:#333;'>
            ".$r['AuthorName']."
        </a>
    </div>"
);

// Users → link to manage_users.php
$users = $conn->query("SELECT Name, Email FROM user WHERE Name LIKE '%$search%' OR Email LIKE '%$search%'");
showResults($users, "👥 Users", fn($r) =>
    "<div style='padding:8px;'>
        <a href='manage_users.php?user=".urlencode($r['Name'])."' style='text-decoration:none; color:#333;'>
            ".$r['Name']." (".$r['Email'].")
        </a>
    </div>"
);

// Borrows → link to borrow_records.php
$borrows = $conn->query("SELECT BorrowID, Status FROM borrow WHERE BorrowID LIKE '%$search%' OR Status LIKE '%$search%'");
showResults($borrows, "📝 Borrow Records", fn($r) =>
    "<div style='padding:8px;'>
        <a href='borrow_records.php?borrowid=".$r['BorrowID']."' style='text-decoration:none; color:#333;'>
            Borrow ID ".$r['BorrowID']." - ".$r['Status']."
        </a>
    </div>"
);

// Fines → link to admin_fines.php
$fines = $conn->query("SELECT Type, Status, Amount FROM fines WHERE Type LIKE '%$search%' OR Status LIKE '%$search%'");
showResults($fines, "💰 Fines", fn($r) =>
    "<div style='padding:8px;'>
        <a href='admin_fines.php?type=".urlencode($r['Type'])."' style='text-decoration:none; color:#333;'>
            ".$r['Type']." - ".$r['Status']." (₱".$r['Amount'].")
        </a>
    </div>"
);

// Materials → link to materials_catalog.php
$materials = $conn->query("SELECT MaterialName, CategoryName FROM materials WHERE MaterialName LIKE '%$search%' OR CategoryName LIKE '%$search%'");
showResults($materials, "🧰 Materials", fn($r) =>
    "<div style='padding:8px;'>
        <a href='materials_catalog.php?material=".urlencode($r['MaterialName'])."' style='text-decoration:none; color:#333;'>
            ".$r['MaterialName']." (".$r['CategoryName'].")
        </a>
    </div>"
);

// Archive → link to archive.php
$archive = $conn->query("SELECT BookTitle, DeletedAt FROM book_archive WHERE BookTitle LIKE '%$search%'");
showResults($archive, "📂 Archive", fn($r) =>
    "<div style='padding:8px;'>
        <a href='archive.php?title=".urlencode($r['BookTitle'])."' style='text-decoration:none; color:#333;'>
            ".$r['BookTitle']." (Deleted: ".$r['DeletedAt'].")
        </a>
    </div>"
);

// Publisher → link to book.php filtered by publisher
$publisher = $conn->query("SELECT PublisherName FROM publisher WHERE PublisherName LIKE '%$search%'");
showResults($publisher, "🏢 Publisher", fn($r) =>
    "<div style='padding:8px;'>
        <a href='book.php?publisher=".urlencode($r['PublisherName'])."' style='text-decoration:none; color:#333;'>
            ".$r['PublisherName']."
        </a>
    </div>"
);

// Categories → link to book.php filtered by category
$categories = $conn->query("SELECT CategoryName FROM categories WHERE CategoryName LIKE '%$search%'");
showResults($categories, "📖 Categories", fn($r) =>
    "<div style='padding:8px;'>
        <a href='book.php?category=".urlencode($r['CategoryName'])."' style='text-decoration:none; color:#333;'>
            ".$r['CategoryName']."
        </a>
    </div>"
);
?>
