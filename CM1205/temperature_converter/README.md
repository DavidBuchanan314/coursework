Originally submitted on 17/03/2017

This coursework was supposed to be completed using some proprietary
piece of software called "Visual Studio", or something like that. I strongly object
to proprietary software being "taught" at educational institutions.
That's not education, it's training. As such, I rigged
together my own source-compatible toolchain using open source tools, documented
here: (along with the source/Makefile for this submission)

https://github.com/DavidBuchanan314/CM1205-toolchain/

The coursework only required us to handle 8-bit integer arithmetic, but I thought
that was a bit boring, so I implemented some floating point parsing/printing routines
taking advantage of the x87 FPU.
