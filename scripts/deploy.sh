#!/bin/bash
echo "Starting deploy script"
if [ -z "$TRAVIS_PULL_REQUEST" ]; then
    echo "Pull request, not deploying.";
    exit
else
    if [ "$TRAVIS_BRANCH" = "master" ]; then
        if [ "$TRAVIS_PHP_VERSION" = "7.0.27" ]; then
            echo "Deploying PHP version $TRAVIS_PHP_VERSION build.";
            cd $TRAVIS_BUILD_DIR
            mkdir build
            mv src templates vendor public build/
            tar -czf vahi.tgz build
            export SSHPASS=$VAHI_DEPLOY_PASS
            sshpass -e scp -o stricthostkeychecking=no vahi.tgz travis@ana.decock.org:/vahi/deploy/data/$TRAVIS_BRANCH/builds
            sshpass -e ssh -o stricthostkeychecking=no travis@ana.decock.org "cd /vahi/deploy/data/$TRAVIS_BRANCH/builds ; tar -xzf vahi.tgz ; rm vahi.tgz ; rm -rf previous ; mv current previous ; mv build current "
            echo "All done.";
        else
            echo "Build on PHP version $TRAVIS_PHP_VERSION, not deploying.";
        fi
    else
        echo "Branch is not master, not deploying."
    fi
fi
echo "Bye"
