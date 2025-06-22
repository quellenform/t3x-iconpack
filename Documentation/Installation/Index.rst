.. include:: /Includes.rst.txt

.. _installation:

============
Installation
============

.. rst-class:: bignums-tip

#. Install this extension from TER or with Composer:

   .. code-block:: bash

      composer require quellenform/t3x-iconpack

#. Install one of the existing iconpack providers:

   .. rst-class:: compact-list

   - `Bootstrap Icons <https://extensions.typo3.org/extension/iconpack_bootstrap>`_
   - `Boxicons <https://extensions.typo3.org/extension/iconpack_boxicons>`_
   - `Dripicons <https://extensions.typo3.org/extension/iconpack_dripicons>`_
   - `Elegant Icons <https://extensions.typo3.org/extension/iconpack_elegant>`_
   - `Feather Icons <https://extensions.typo3.org/extension/iconpack_feather>`_
   - `Flag Icons <https://github.com/CMS-Internetsolutions/iconpack_flagicons>`_
   - `Font Awesome <https://extensions.typo3.org/extension/iconpack_fontawesome>`_
   - `Helium Icons <https://extensions.typo3.org/extension/iconpack_helium>`_
   - `Iconoir Icon <https://github.com/quellenform/t3x-iconpack-iconoir>`_
   - `Ikons vector icons <https://extensions.typo3.org/extension/iconpack_ikons>`_
   - `Ionicons <https://extensions.typo3.org/extension/iconpack_ionicons>`_
   - `Linea Icons <https://extensions.typo3.org/extension/iconpack_linea>`_
   - `Linearicons <https://extensions.typo3.org/extension/iconpack_linearicons>`_
   - `Lineicons <https://extensions.typo3.org/extension/iconpack_lineicons>`_
   - `Octicons <https://extensions.typo3.org/extension/iconpack_octicons>`_
   - `Tabler Icons <https://github.com/quellenform/t3x-iconpack-tabler-icons>`_
   - `Themify Icons <https://extensions.typo3.org/extension/iconpack_themify>`_
   - `TYPO3 Icons <https://github.com/quellenform/t3x-iconpack-typo3>`_

   .. rst-class:: horizbuttons-tip-m

      - :ref:`...or create your own iconpack provider <customIconpack>`

#. Add the provided TypoScript to your template

   (Make sure that `lib.parseFunc_RTE` is not overwritten by any subsequent templates!)

#. [Optional] Install the `Iconpack for Bootstrap Package
   <https://extensions.typo3.org/extension/bootstrap_package_iconpack>`_
   extension

   ...if you want to use Iconpack with `Bootstrap Package
   <https://extensions.typo3.org/extension/bootstrap_package>`_ and want to
   replace the hard-coded icons with a better and flexible system.

.. note::
   If you use `EXT:bootstrap_package_iconpack
   <https://github.com/quellenform/t3x-bootstrap-package-iconpack>`_, make sure
   you include the templates **at the end**, otherwise `lib.parseFunc_RTE` will be
   overwritten by `EXT:bootstrap_package
   <https://github.com/benjaminkott/bootstrap_package/>`_ and the icons cannot
   be displayed.

.. note::
   If you are using your own templates for the header, please note that a
   ViewHelper is used to display icons. In this case, simply take a look at
   the supplied template and migrate these settings.
