<?php

namespace Snowdog\Academy\Model;

use Snowdog\Academy\Core\Database;

class BookManager
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function create(string $title, string $author, string $isbn, int $for_adults = 0): int
    {   
        $statement = $this->database->prepare('INSERT INTO books (title, author, isbn, for_adults) VALUES (:title, :author, :isbn, :for_adults)');
        $binds = [
            ':title' => $title,
            ':author' => $author,
            ':isbn' => $isbn,
            ':for_adults' => $for_adults
        ];
        $statement->execute($binds);

        return (int) $this->database->lastInsertId();
    }

    public function update(int $id, string $title, string $author, string $isbn, int $for_adults): void
    {   
        $statement = $this->database->prepare('UPDATE books SET title = :title, author = :author, isbn = :isbn, for_adults = :for_adults WHERE id = :id');
        $binds = [
            ':id' => $id,
            ':title' => $title,
            ':author' => $author,
            ':isbn' => $isbn,
            ':for_adults' => $for_adults
        ];

        $statement->execute($binds);
    }

    public function getBookByDay(int $day){

        $query = $this->database->prepare('SELECT id, title, author, isbn, borrowed, borrowed_at FROM books  INNER JOIN borrows ON books.id = book_id AND borrowed_at < NOW()- INTERVAL :day DAY');
        $query->setFetchMode(Database::FETCH_CLASS, Book::class);
        $query->execute([':day' => $day]);

        return $query->fetchAll(Database::FETCH_CLASS, Book::class);
    }

    public function getBookById(int $id)
    {
        $query = $this->database->prepare('SELECT * FROM books WHERE id = :id');
        $query->setFetchMode(Database::FETCH_CLASS, Book::class);
        $query->execute([':id' => $id]);

        return $query->fetch(Database::FETCH_CLASS);
    }

    public function getAllBooks(): array
    {
        $query = $this->database->query('SELECT * FROM books');

        return $query->fetchAll(Database::FETCH_CLASS, Book::class);
    }

    public function getAvailableBooks(): array
    {
        $query = $this->database->query('SELECT * FROM books WHERE borrowed = 0');

        return $query->fetchAll(Database::FETCH_CLASS, Book::class);
    }

    public function getAvailableBooksForChild(): array
    {
        $query = $this->database->query('SELECT * FROM books WHERE borrowed = 0 and for_adults=0');

        return $query->fetchAll(Database::FETCH_CLASS, Book::class);
    }

}
