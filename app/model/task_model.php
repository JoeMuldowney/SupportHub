<?php


/**
 * Class Task
 *
 * Represents a support or workflow task within the system.
 * Encapsulates task details, status, associated users, and any attached images.
 */

class Task
{
    /**
     * @var array<string> Holds multiple filenames associated with this task
     */
    private $taskImages = []; 
    
    public function __construct(        
        private ?int    $id,
        private int     $user_id,
        private string  $location,
        private string  $priority,
        private string  $status,
        private string  $user_desc,
        private string  $date_opened,
        private ?string $date_updated,
        private ?string $date_closed,
        private ?string $solution,
        private ?string $opened_by,
        private ?string $updated_by,
        private ?string $closed_by,
        private ?string $category,        
        private ?string $manager_email,
    ) {}
    

    // -------------------- Getters --------------------

    public function getTaskID(): ?int
    {
        return $this->id;
    }

    public function getUserID(): int
    {
        return $this->user_id;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function getPriority(): string
    {
        return $this->priority;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getUserDesc(): string
    {
        return $this->user_desc;
    }

    public function getDateCreated(): DateTime
    {
        return new DateTime($this->date_opened);
    }

    public function getDateUpdated(): ?DateTime
    {
        return $this->date_updated ? new DateTime($this->date_updated) : null;
    }

    public function getDateClosed(): ?DateTime
    {
        return $this->date_closed ? new DateTime($this->date_closed) : null;
    }

    public function getSolution(): ?string
    {
        return $this->solution;
    }

    public function getOpenedBy(): ?string
    {
        return $this->opened_by;
    }

    public function getUpdatedBy(): ?string
    {
        return $this->updated_by;
    }

    public function getClosedBy(): ?string
    {
        return $this->closed_by;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function getManagerEmail(): ?string
    {
        return $this->manager_email;
    }
    // --------------------------------------------------------------
    // -------------------- Setters -------------------------------
    // ----------------------------------------------------------

    public function setTaskID(int $id): void
    {
        $this->id = $id;
    }

    public function setUserID(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    public function setLocation(string $location): void
    {
        $this->location = $location;
    }

    public function setPriority(string $priority): void
    {
        $this->priority = $priority;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function setUserDesc(string $user_desc): void
    {
        $this->user_desc = $user_desc;
    }

    public function setDateCreated(DateTime $date_opened): void
    {
        $this->date_opened = $date_opened->format("Y-m-d");
    }

    public function setDateUpdated(?DateTime $date_updated): void
    {
        $this->date_updated = $date_updated?->format("Y-m-d");
    }

    public function setDateClosed(?DateTime $date_closed): void
    {
        $this->date_closed = $date_closed?->format("Y-m-d");
    }

    public function setSolution(?string $solution): void
    {
        $this->solution = $solution;
    }

    public function setOpenedBy(?string $opened_by): void
    {
        $this->opened_by = $opened_by;
    }

    public function setUpdatedBy(?string $updated_by): void
    {
        $this->updated_by = $updated_by;
    }

    public function setClosedBy(?string $closed_by): void
    {
        $this->closed_by = $closed_by;
    }

    public function setCategory(?string $category): void
    {
        $this->category = $category;
    }


    public function setManagerEmail(?string $manager_email): void
    {
        $this->manager_email = $manager_email;
    }

    // --------------------------------------------------------
    // -------------------- Image Handling --------------------
    // --------------------------------------------------------

    //method to add image names to the taskImages array
    public function addTaskImage(string $imageName): void
    {
        $this->taskImages[] = $imageName;
    }

    //method to get all image names associated with the task
    public function getTaskImages(): array
    {
        return $this->taskImages;
    }

    //method to clear all image names from the taskImages array
    public function clearTaskImages(): void
    {
        $this->taskImages = [];
    }   

    
}
