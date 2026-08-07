.. include:: /Includes.rst.txt

.. _history:

======================
Additional Information
======================



Why I made this extension?
--------------------------

...because this feature is simply missing in TYPO3!

Various existing extensions have so far only ever handled a single iconpack, and
even that was not optimally integrated into TYPO3. Most of them can either only
be used in the RTE, and others only in a single additional field. All extensions
so far also lack the possibility to influence the icon rendering afterwards.
Furthermore, other extensions don't really offer the possibility to use an icon
set flexibly in their own database fields and to achieve a consistent rendering
across the whole website.

It took me several months to find out how an optimal flexible iconpack system
should work. There is still room for improvement in the programming, but I tried
to create a mechanism that offers the greatest possible flexibility and
consistency for current and future requirements by analyzing various icon sets
and extensive testing.

The main focus for me was that every possible icon set should be able to be used
with it, and at the same time it should be possible to use it in all TYPO3
fields (native fields, RTE, fields in own extensions, ...).

Another focus was on the extensibility and modification of existing iconpack
extensions. These should be integrated into the system as easy as possible (YAML
file).



Why I published this extension?
-------------------------------

I wrote this extension at the end of 2020, and unfortunately didn't make it
public right then.

The reason why the whole thing is now published by me after all is that I am
convinced that such a system can really help to improve and simplify the
handling with icons in TYPO3.

If you think that this is also a step in the right direction for you and you
have wishes, thanks or improvements, please share your :ref:`contribution <contribution>`!
