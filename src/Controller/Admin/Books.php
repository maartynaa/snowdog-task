<?php

namespace Snowdog\Academy\Controller\Admin;

use Snowdog\Academy\Model\Book;
use Snowdog\Academy\Model\BookManager;

class Books extends AdminAbstract
{
    private BookManager $bookManager;
    private ?Book $book;

    public function __construct(BookManager $bookManager)
    {
        parent::__construct();
        $this->bookManager = $bookManager;
        $this->book = null;
    }

    public function index(): void
    {
        require __DIR__ . '/../../view/admin/books/list.phtml';
    }

    public function newBook(): void
    {
        require __DIR__ . '/../../view/admin/books/add.phtml';
    }

    public function importBooks(): void
    {
        require __DIR__ . '/../../view/admin/books/import.phtml';
    }

    public function borrowedBooks(): void
    {
        require __DIR__ . '/../../view/admin/books/borrowed.phtml';
    }

    public function importBooksPost(): void
    {
        $file = $_FILES['file']['tmp_name'];
        $handle = fopen($file, "r");

        $flag = true;
        while (($content = fgetcsv($handle, 10000, ",")) != false)
        {
            if($flag) { $flag = false; continue; }
            $title = $content[0];
            $author = $content[1];
            $isbn = $content[2];


            $this->bookManager->create($title, $author, $isbn);
        }


        $_SESSION['flash'] = "File imported successfully";
        header('Location: /admin');

    }

    public function newBookPost(): void
    {
        
        $isbn = $_POST['isbn'];
        $for_adults = $_POST['adult'];

        if ($for_adults == 1){
            $for_adults = true;
        }
        else {
            $for_adults = false;
        }


        if(isset($_POST['submit'])){

            $path = 'https://openlibrary.org/api/books?bibkeys='. $isbn . '&jscmd=details&format=json';

            $json = file_get_contents($path);
            $arr = json_decode($json, true);

            if(empty($arr)){
                $_SESSION['flash'] = 'There is no such ISBN';
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                return;
            }
             
            $title = $arr[$isbn]["details"]["title"];
            $author = $arr[$isbn]["details"]["authors"][0]["name"];            
        }

        else {
            $title = $_POST['title'];
            $author = $_POST['author'];
            
        }


        if (empty($title) || empty($author) || empty($isbn)) {
            $_SESSION['flash'] = 'Missing data';
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            return;
        }

        $this->bookManager->create($title, $author, $isbn, $for_adults);

        $_SESSION['flash'] = "Book $title by $author saved!";
        header('Location: /admin');
        
    }

    public function edit(int $id): void
    {
        $book = $this->bookManager->getBookById($id);
        if ($book instanceof Book) {
            $this->book = $book;
            require __DIR__ . '/../../view/admin/books/edit.phtml';
        } else {
            header('HTTP/1.0 404 Not Found');
            require __DIR__ . '/../../view/errors/404.phtml';
        }
    }

    public function editPost(int $id): void
    {
        $title = $_POST['title'];
        $author = $_POST['author'];
        $isbn = $_POST['isbn'];
        $for_adults = $_POST['adult'];
        if ($for_adults == 1){
            $for_adults = true;
        }
        else {
            $for_adults = false;
        }

        if (empty($title) || empty($author) || empty($isbn)) {
            $_SESSION['flash'] = 'Missing data';
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            return;
        }

        $this->bookManager->update($id, $title, $author, $isbn, $for_adults);

        $_SESSION['flash'] = "Book $title by $author saved!";
        header('Location: /admin');
    }

    private function getBooks(): array
    {
        return $this->bookManager->getAllBooks();
    }


    private function getFilterBooks(int $day): array
    {  
        return $this->bookManager->getBookByDay($day);
    }

    
}
