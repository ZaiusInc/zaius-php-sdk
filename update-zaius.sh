#!/bin/bash 

#0 global settings

M1_REPO='git@bitbucket.org:trellisboston/zaius-magento.git'
M2_REPO='git@bitbucket.org:trellisboston/zaius-magento2.git'

# 1. Check required system tools
_check_installed_tools() {
    local missed=""

    until [ -z "$1" ]; do
        type -t $1 >/dev/null 2>/dev/null
        if (( $? != 0 )); then
            missed="$missed $1"
        fi
        shift
    done

    echo $missed
}

REQUIRED_UTILS='git diff'
MISSED_REQUIRED_TOOLS=`_check_installed_tools $REQUIRED_UTILS`
if (( `echo $MISSED_REQUIRED_TOOLS | wc -w` > 0 ));
then
    echo -e "Error! Some required system tools, that are utilized in this sh script, are not installed:\nTool(s) \"$MISSED_REQUIRED_TOOLS\" are missed, please install."
    exit 1
fi


# 2. Determine bin path for system tools
CAT_BIN=`which cat`
GIT_BIN=`which patch`
SED_BIN=`which sed`
PWD_BIN=`which pwd`
BASENAME_BIN=`which basename`

BASE_NAME=`$BASENAME_BIN "$0"`
CURRENT_DIR=`$PWD_BIN`/


# 3. Help menu
if [ "$1" = "-?" -o "$1" = "-h" -o "$1" = "--help" ]
then
    $CAT_BIN << EOFH
Usage: /bin/bash $BASE_NAME [--help] [-F|--force] gitref
Checks out the specified gitref version of the Zaius module.

-F, --force    Force rewriting files without any prompts
--help          Show this help message
EOFH
    exit 0
fi

# 3. Get Magento version
function _get_magento_version() {
    if [ -f `echo "$CURRENT_DIR""app/etc/local.xml"` ]; then
        echo "M1"
        return
    fi
    if [ -f `echo "$CURRENT_DIR""app/etc/env.php"` ]; then
        echo "M2"
        return
    fi
    echo 'NONE'
}

MAGENTO_VERSION=$(_get_magento_version);

if [ "$MAGENTO_VERSION" = "NONE" ]; then
    echo -e "No Magento installation found. Make sure you run the command in a Magento installation root directory"
    exit 1
fi

#4 checkout the correct version of the Zaius module
if [ "$MAGENTO_VERSION" = "M1" ]; then
    REPO=$M1_REPO
else
    REPO=$M2_REPO
fi

CHECKOUT_DIR=`mktemp -d`
if [[ ! "$CHECKOUT_DIR" || ! -d "$CHECKOUT_DIR" ]]; then
  echo "Could not create temp dir"
  exit 1
fi

#Script cleanup - delete the temporary checkout directory
function cleanup {
  rm -rf "$CHECKOUT_DIR"
}
trap cleanup EXIT

if [ -z "$1" ]
then
    GITREF="HEAD"
else
    GITREF=$1
fi

`git clone $REPO $CHECKOUT_DIR`
IS_BRANCH=$(cd $CHECKOUT_DIR  && git branch -a | grep "$GITREF$")
if [ -z "$IS_BRANCH" ]
then
    #a commit ref
    cd $CHECKOUT_DIR && git reset --hard $GITREF
else
    #a branch
    cd $CHECKOUT_DIR && git fetch && git checkout -b $GITREF remotes/origin/$GITREF
fi


#diff the repo contents with the webroot contents, copy the Zaius module
if [ "$MAGENTO_VERSION" = "M1" ]; then
    REPO_SRC_ROOT="$CHECKOUT_DIR"/app/
    MAGENTO_SRC_ROOT="$CURRENT_DIR"app/
else
    REPO_SRC_ROOT="$CHECKOUT_DIR"/src/
    MAGENTO_SRC_ROOT="$CURRENT_DIR"app/code/Zaius/Engage/
fi


if [ ! -d "$MAGENTO_SRC_ROOT" ]; then
    echo "No Zaius copy found, proceeding with installation..."
    mkdir -p $MAGENTO_SRC_ROOT && cp -r $REPO_SRC_ROOT/* $MAGENTO_SRC_ROOT
else
    DIFFOUTPUT=$(diff --brief -r $MAGENTO_SRC_ROOT $REPO_SRC_ROOT | grep -v "^Only in $MAGENTO_SRC_ROOT")
    if [[ $DIFFOUTPUT ]]; then
        echo "$DIFFOUTPUT"
        read -p "The specified Zaius module is different from the version you have locally. Are you sure you want to override?" yn
        case $yn in
            [Yy]* ) cp -r $REPO_SRC_ROOT/* $MAGENTO_SRC_ROOT; echo "The Zaius module was updated to the specified version";;
            [Nn]* ) exit;;
            * ) echo "Please answer yes or no.";;
        esac
    else
        echo "The Zaius module is already at the specified version"
    fi
fi

