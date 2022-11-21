#!/bin/sh

reset="\e[0m"
expand="\e[K"

notice="\e[1;33;44m"
success="\e[1;33;42m"
fail="\e[1;33;41m"

function section() {
  SECTION=$1
  echo -e "${notice} $1 ${expand}${reset}"
}

function status() {
  RC=$?
  if [ "$RC" == "0" ] ; then
    echo -e "${success} SUCCESS: ${SECTION} ${expand}${reset}\n"
  else
    echo -e "${fail} ERROR($RC): ${SECTION} ${expand}${reset}\n"
  fi
}

trap "status" EXIT
