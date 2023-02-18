#!/usr/bin/env bash
# -------------------------------------------------------------------
# Builds documentation.
#
# Usage:
# build [options]
#
# Options :
#  -c File :          Read configuration from the given file.
#                     If no configuration file given tries to build with
#                     sensible defaults.
#
# -------------------------------------------------------------------
while getopts c: flag
do
    case "${flag}" in
        c) configuration=${OPTARG};;
        *) echo; exit 1;;
    esac
done
if [ -z ${configuration+x} ]; then configuration=""; fi;

# build rst-files from code, using configuration file or defaults.
php doc2rst.php --configuration_file="$configuration"
exit_code=$?
# echo -e "exit_code from doc2rst=$exit_code\n"
if [ $exit_code != 0 ]; then exit $exit_code; fi;

echo "sphinx";
make html
