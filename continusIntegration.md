# Requiements #

  * hudson
  * ant

# Plugins to install #

  * Checkstyle Plugin
  * Hudson Google Code plugin
  * Hudson Emma plugin

# Script #

```
./symfony sr-test:checkstyle --xml="$WORKSPACE/log/checkstyle.xml"
./symfony sr-test:unit --xml="log/unit.xml"
./symfony sr-test:coverage-report --xml="log/coverage.xml"
rm -rf [^log]*
cp  /home/agallou/workspace/diff/build.xml $WORKSPACE/build.xml
```