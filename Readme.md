[![CircleCI](https://img.shields.io/circleci/project/github/Firesphere/silverstripe-partial-userforms.svg)](https://circleci.com/gh/Firesphere/silverstripe-partial-userforms)
[![codecov](https://codecov.io/gh/Firesphere/silverstripe-partial-userforms/branch/master/graph/badge.svg)](https://codecov.io/gh/Firesphere/silverstripe-partial-userforms)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Firesphere/silverstripe-partial-userforms/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Firesphere/silverstripe-partial-userforms/?branch=master)

# Partial User Defined Form submissions

This module aims to store partial submissions of userforms, for where the User Defined Form has multiple "pages".

The partials are stored and removed when the form is finished.

# Installation

`composer require firesphere/partialuserforms`

# Usage

Partial form submissions are stored in the database and visible on the forms in the CMS.

A daily export of the partial submissions can be acquired by checking the checkbox on the form
and setting the configuration in the Settings

# Requirements

- UserDefinedForms 5+
- SilverStripe Recipe CMS 1+
- QueuedJobs 4+

## Dev requirements

- PHPUnit
- Immediate exception printer
- PHP CodeSniffer

# Benefits

With Partial forms, partial submissions are available to CMS users, to see how far a visitor got through the form
and optionally make a call to the user, asking why they gave up.

# Further improvements

- Save the partial form to LocalStorage, to enable pre-filling of forms
- JS test coverage

# Actual license

This module is published under BSD 3-clause license, although these are not in the actual classes, the license does apply:

http://www.opensource.org/licenses/BSD-3-Clause

Copyright (c) 2012-NOW(), Simon "Firesphere" Erkelens

All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

    Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
    Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.


# Did you read this entire readme? You rock!

Pictured below is a cow, just for you.
```

               /( ,,,,, )\
              _\,;;;;;;;,/_
           .-"; ;;;;;;;;; ;"-.
           '.__/`_ / \ _`\__.'
              | (')| |(') |
              | .--' '--. |
              |/ o     o \|
              |           |
             / \ _..=.._ / \
            /:. '._____.'   \
           ;::'    / \      .;
           |     _|_ _|_   ::|
         .-|     '==o=='    '|-.
        /  |  . /       \    |  \
        |  | ::|         |   | .|
        |  (  ')         (.  )::|
        |: |   |;  U U  ;|:: | `|
        |' |   | \ U U / |'  |  |
        ##V|   |_/`"""`\_|   |V##
           ##V##         ##V##
```
