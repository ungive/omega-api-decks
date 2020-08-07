#!/bin/bash

BIN="$1"

function create_script() {
cat << EOF
#!/bin/bash
cd "$(pwd)"
php "$script"
EOF
}

# note: the glob pattern is relative to the root directory of the project

for script in scripts/*.php; do
    NAME="${script%.*}"
    FILE="$BIN/$NAME"
    create_script "$script" > $FILE
    chmod +x "$FILE"
done
