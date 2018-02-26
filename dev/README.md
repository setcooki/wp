# setcooki-wp dev template

This is the basic starter template for setcooki-wp development and testing. everything is bundled as gulp task

## Requirements

- node >= 0.10 (https://docs.npmjs.com/getting-started/installing-node)
- bower global install (https://bower.io/#install-bower)

## Installation

Install dependencies:

```bash
bash ./install
```

Finally, run `npm start` to run the compile/build. It will re-run every time you save a sass file.

## Usage

The development and test theme and plugin are located in this directory under `dev/skeletons`. In order to use the test
skeletons you need to symlink or copy the theme + plugin directory to your wordpress install. Running `bash setup` from repo root
will install wordpress and copy the skeletons for development and test purposes. The skeletons are detached then - changes
in copy are not reflected/updated in the original skeletons in `dev/skeletons`. In order to extend/change the skeletons and
have it run against the wordpress install you need to run the dev mode change script in this directory by running `bash ./dev on`.
This will symlink the skeletons from `dev/skeletons` to the wordpress install and keep the copied skeletons as backup in the
background as long as you dont run `bash ./dev off` again. So changes made in the skeleton copy are not lost.