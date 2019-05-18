# SelfLock storage

For run example add right permission temp/ directory.

```bash
chmod 777 temp/
``` 

Open browser and execute in four tabs during 4s this `run.php`.

In this moment is `FileStorage` active. You will see in browser number 1,2,3 and 4. Tabs are waiting for working tab to end.

```bash
tab 1
- working sleep 5s
- write to cache 1
tab 2 
- working sleep 5s 
- write to cache 2
tab 3
- working sleep 5s 
- write to cache 3
tab 4
- working sleep 5s 
- write to cache 4

total execute time is cca 20s
```


Second execute four tabs `run.php?apcu=1`.
In this moment is `APCUStorage` active. You will see in tabs number 1, each tab and first tab end to work and other tabs read data from cache.
```bash
tab 1
- working sleep 5s
- write to cache 1
tab 2 
- read from cache 1
tab 3
- read from cache 1
tab 4
- read from cache 1

total execute time is cca 5s
```