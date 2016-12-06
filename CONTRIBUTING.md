Prepare your development environment
------------------------------------

The following steps will create a development environment. These steps only need to be done the first time you contribute.

### 1. [Fork](http://help.github.com/fork-a-repo/) this repository on GitHub and clone your fork to your development environment

```
git clone git@github.com:YOUR-GITHUB-USERNAME/yii2-db-rbac.git
```


### 2. Add the main repository as an additional git remote called "upstream"

Change to the directory where you cloned. Then enter the following command:

```
git remote add upstream git://github.com/developeruz/yii2-db-rbac.git
```


Working on bugs and features
----------------------------

Having prepared your develop environment as explained above you can now start working on the feature or bugfix.

### 1. Fetch the latest code from the main repository

```
git fetch upstream
```

You should start at this point for every new contribution to make sure you are working on the latest code.

### 2. Create a new branch for your feature based on the current master branch

Each separate bug fix or change should go in its own branch. Branch names should be descriptive.
For example:

```
git checkout upstream/master
git checkout -b name-of-your-branch-goes-here
```

### 3. Do your magic, write your code

Make sure it works :)

### 4. Update the CHANGELOG

Edit the CHANGELOG file to include your change. The line in the change log should look like one of the following:

```
Bug: a description of the bug fix (Your Name)
Enh: a description of the enhancement (Your Name)
```


### 5. Commit your changes

add the files/changes you want to commit to the [staging area](http://gitref.org/basic/#add) with

```
git add path/to/my/file.php
```

Commit your changes with a descriptive commit message.

```
git commit -m "A brief description of this change"
```

### 6. Pull the latest Yii code from upstream into your branch

```
git pull upstream master
```

This ensures you have the latest code in your branch before you open your pull request. If there are any merge conflicts,
you should fix them now and commit the changes again. This ensures that it's easy to merge your changes with one click.

### 7. Having resolved any conflicts, push your code to GitHub

```
git push -u origin name-of-your-branch-goes-here
```

### 8. Open a [pull request](http://help.github.com/send-pull-requests/) against upstream.

Go to your repository on GitHub and click "Pull Request", choose your branch on the right and enter some more details
in the comment box. 

### Thank you for cooperation!###
