
# Hungarian algorithm / Munkres algorithm - php solution  
A php implementation of the Hungarian/Munkres Algorithm for task assignment.   
Please refer to http://csclab.murraystate.edu/bob.pilgrim/445/munkres.html for more details about the algorithm.   
  
## Some practices
  
In general, the Munkres's algorithm deals with task assignment problems where the number of tasks N equals the number of workers M. However, in practice, this may not be true. In the case where N < M, the typical work around is inject N-M dummy tasks with high costs for each worker. Handing the case where N > M is more complicated depending more on domain knowledge. There are usually two solutions. The first is to do the assignment in multiple rounds where in each round we have N <= M. However, one need to carefully determine the order of task assignment carefully. The other is to group the N tasks into N'<=M clusters and calculate the costs between task clusters and workers. To my opinion, the latter sounds more reasonable.  
  
## How to use  
  
Create a matrix indexed by a person id which contains an array indexed by task/slot id and the task cost/slot preference. For example:
```php  
$matrix = [  
  0 => [  // index = person_id
    0 => 2,  // index = task/slot id, value = cost/preference
    1 => 1,  
    2 => 1,  
    3 => 3,  
    4 => 3,  
    5 => 999,  
  ]  
];
```
Now we are going to initiliaze the matrix in the service and run the algorithm.
```php
$ha = new HungarianAlgorithm();  
$ha->initData($matrix);  
$result = $ha->runAlgorithm();
```
The result will be the same matrix as the one we've inputted, only, now the costs/preferences have been changed to zeros and a one. The value for which we retrieve a "1", is the chosen task/slot for this person.
```php  
$matrix = [  
  0 (person_id) => [  
    0 => 0,  
    1 => 0,  
    2 => 0,  
    3 => 0,  
    4 => 1,  // chosen task/slot
    5 => 0,  
  ]  
];
```

## Performance test (comparison)
  
By running the test file and modifying the amount of students, you can clearly see what benefit the Hungarian algorithm offers compared to more "classic methods" like calculating all possible combinations.  
The example in the test file consists of a given amount of students that can all pass their preferred exam date (top 3). Then we will calculate the best possible solution with the given data.