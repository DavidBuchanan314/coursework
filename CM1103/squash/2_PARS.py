from functools import reduce
import numpy as np
import matplotlib as mpl
import matplotlib.colors as colors
import matplotlib.pyplot as plt

WIN_THRESHOLD = 11
REQUIRED_LEAD = 2
MAX_DURATION = WIN_THRESHOLD*2 - REQUIRED_LEAD
TOTAL_DURATION = 50
HORIZONAL_RESOLUTION = 2048
ALMOST_ZERO=10e-9

xedges = np.linspace(0.0, 1.0, num=HORIZONAL_RESOLUTION+1)
yedges = np.linspace(-0.5, TOTAL_DURATION-0.5, num=TOTAL_DURATION+1)
H = np.full((TOTAL_DURATION, HORIZONAL_RESOLUTION), ALMOST_ZERO)

expectations = [None]*len(xedges)

def ncr(n, r):
	r = min(r, n-r)
	if r == 0: return 1
	numer = reduce(lambda a, b: a*b, range(n, n-r, -1))
	denom = reduce(lambda a, b: a*b, range(1, r+1))
	return numer//denom

for rarb in xedges[:-1]:
	rb = 1
	ra = rarb*rb
	r = ra/(ra+rb)
	n = int(rarb*HORIZONAL_RESOLUTION)

	remaining = 1

	for game_length in range(WIN_THRESHOLD, MAX_DURATION+1):
		score_of_loser = game_length - WIN_THRESHOLD
		probability = (r**WIN_THRESHOLD * (1-r)**score_of_loser +
			r**score_of_loser * (1-r)**WIN_THRESHOLD) * ncr(game_length-1, WIN_THRESHOLD-1)
		H[game_length, n] = probability
		remaining -= probability

	"""
	the remaining probability is the chance of reaching 10/10

	The only way the game can end is if one player scores twice in a row
	"""

	for length in range(MAX_DURATION+2, TOTAL_DURATION, 2):
		if remaining < ALMOST_ZERO:
			break
		prob = remaining * (r**2 + (1-r)**2)
		H[length, n] = prob
		remaining -= prob
		
	probs = H[:,n]
	expectations[n] = np.average(list(range(len(probs))), weights=probs)

X, Y = np.meshgrid(xedges, yedges)
plt.figure(figsize=(16,10))
plt.pcolormesh(X, Y, H, cmap="cubehelix_r",
	norm=colors.LogNorm(0.001, 1.0)).set_rasterized(True)
plt.axis([X.min(),X.max(),Y.min(),Y.max()])
plt.plot(xedges, expectations, lw=5, c="cyan")
plt.colorbar(label="Probability")
plt.grid()
plt.title("PARS")
plt.xlabel("ra/rb")
plt.ylabel("Game Length (rallies)")
plt.savefig("PARS.svg", dpi=300)
