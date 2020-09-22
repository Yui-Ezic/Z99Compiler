# Z99Compiler
Compiler for Z99 language

You can find examples and syntax in [documentation](/docs/ТР-71.Курсова.ЗуєвМО.pdf)

## Get started
1. Clone the repository. Run composer install.
2. Write program on Z99 language or choose any example from example folder.
3. Run ```php compiler.php run PATH_TO_YOUR_Z99_PROGRAM```

## Example
File: example\positiveAverage.z99
``` 
program positiveAverage
var i: int;
    sum, value: real;
begin
    sum = 0.0;
    i = 1;
    repeat
        read (value);
        if value < 0 then
            value = -value;
        fi;
        sum = sum + value;
        i = i + 1;
    until i <= 3;
    sum = sum / 3;
    write(sum);
end.
```
Run
```
php compiler.php run example\positiveAverage.z99
```
Input the values and see result
```
10
-10
20
13.333333333333
```
