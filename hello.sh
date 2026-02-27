# Find files that are modified in the pull request
          MODIFIED_FILES=$(git diff --name-only 0690e1040e13a0f67e68b2023323b78cdd887ea8 | grep -v 'code/aspen_app')

          # Loop through each file and check for spaces used instead of tabs
          EXIT_CODE=0
          declare -A RESULTS
          for file in $MODIFIED_FILES; do
            echo "Found modified file: $file";
            if [[ $file == *.php || $file == *.js || $file == *.java ]]; then
              DIFF=$(git diff 0690e1040e13a0f67e68b2023323b78cdd887ea8 -- $file)
              #echo "DIFF: $DIFF"
              while IFS= read -r RAW_LINE; do
                if [[ $RAW_LINE =~ ^\+ ]]; then
                    LINE=$(echo -e "$RAW_LINE" | sed 's/\t/_TAB_/g')
                    if [[ $LINE =~ ^\+(_TAB_)*[[:space:]][[:space:]]+ ]]; then
                      echo "Bad Line: $RAW_LINE"
                      RESULTS[$file]=1
                      EXIT_CODE=1
                    fi
                fi
              done <<< "$DIFF"
            fi
          done

          if [ $EXIT_CODE -eq 1 ]; then
            echo "FILES CONTAINING SPACES INSTEAD OF TABS:"
            for key in "${!RESULTS[@]}"; do
                echo "$key"
            done
          else
            echo "NO SPACES FOUND!";
          fi

          exit $EXIT_CODE
