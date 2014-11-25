#!/bin/bash

TESTDIR="$(dirname $0)"

FALLBACK_OX_PATH="/var/www/oxid/source"
FALLBACK_OX_METADATA="$TESTDIR/../../metadata.php"

cd "$TESTDIR"

__DEBUG=""
__OPTIONS=""
__VERBOSE="--verbose"
__COVERAGE=""
#__CONFIG="--configuration=mf_cushymocoTestsUnit.xml"
__FILTER=""
__BOOTSTRAP=""
__START="./unit"
__ARGUMENTS=""

showHelp() {
    echo "Usage:"
    echo "    $(basename $0) [<options>]"
    echo ""
    echo "Options:"
    echo "    --no-verbose                    No verbose output"
    echo "    --verbose=[no|off|false]        Alias for --no-verbose"
    echo "    --run-coverage             -c   Alias for --coverage-html \"\${TESTDIR}/coverage\""
    echo "    --debug                    -d   Use env-variable 'XDEBUG_CONFIG=\"idekey=phpunit\"'"
    echo "    --help                     -h   Show this help"
    echo ""
    echo "You can also use all options of php-unit cli (see 'phpunit --help')."
    echo ""
}

for option in "$@"; do
    case $option in
        '--verbose=no' | '--verbose=off' | '--verbose=false'|'--no-verbose')
            __VERBOSE=""
            ;;
        '--run-coverage' | '-c')
            rm -rf "$TESTDIR/coverage"
            __COVERAGE="--coverage-html \"${TESTDIR}/coverage\""
            ;;
#        '--configuration='*)
#            __CONFIG="$option"
#            ;;
#        '--bootstrap='*)
#            __BOOTSTRAP="$option"
#            ;;
#        '--start='*)
#            __START="${option:8}"
#            ;;
        '--debug' | '-d')
            __DEBUG="XDEBUG_CONFIG=\"idekey=PHPSTORM\""
            ;;
        '--help' | '-h')
            showHelp
            exit
            ;;
        *)
            __OPTIONS="$__OPTIONS $option"
            ;;
    esac
done

echo "Use flag -c to run coverage."
echo ""

oxADMIN_PASSWD="${oxADMIN_PASSWD:-"password"}"
OXID_VERSION="${OXID_VERSION:-"CE"}"
oxPATH="${oxPATH:-}"
oxMETADATA="${oxMETADATA:-}"

[ "$oxPATH" == '' ] && [ -d "$FALLBACK_OX_PATH" ] \
    && oxPATH="$FALLBACK_OX_PATH"

[ "$oxMETADATA" == '' ] &&  [ -f "$FALLBACK_OX_METADATA" ] \
    && oxMETADATA="$FALLBACK_OX_METADATA"

cmd=$(echo "$__DEBUG" \
    oxADMIN_PASSWD="$oxADMIN_PASSWD" \
    oxMETADATA="$oxMETADATA" \
    OXID_VERSION="$OXID_VERSION" \
    oxPATH="$oxPATH" \
    php -d 'memory_limit=1024M' $(which phpunit) \
        $__OPTIONS $__VERBOSE $__COVERAGE $__CONFIG $__FILTER $__BOOTSTRAP $__START
)

echo -ne "\e[1m==> " # bold
echo -n $cmd
echo -e "\e[0m"      # bold end
echo ""

eval "$cmd"