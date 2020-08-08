#!/bin/bash

BIN="$1"

function create_script() {
cat << EOF
#!/bin/bash
cd "$(pwd)"
php "$script"
EOF
}

for script in *.php; do
    NAME="${script%.*}"
    FILE="$BIN/$NAME"
    create_script "$script" > $FILE
    chmod +x "$FILE"
done
