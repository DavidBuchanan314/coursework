import numpy as np
import matplotlib as mpl
import matplotlib.colors as colors
import matplotlib.pyplot as plt

END_SCORE = 9
TOTAL_DURATION = 50
HORIZONAL_RESOLUTION = 1024
ALMOST_ZERO = 10e-9

xedges = np.linspace(0.0, 1.0, num=HORIZONAL_RESOLUTION+1)
yedges = np.linspace(-0.5, TOTAL_DURATION-0.5, num=TOTAL_DURATION+1)
H = np.full((TOTAL_DURATION, HORIZONAL_RESOLUTION), ALMOST_ZERO)

expectations = [None]*len(xedges)

for rarb in xedges[:-1]:
	rb = 1
	ra = rarb*rb
	r = ra/(ra+rb)
	n = int(rarb*HORIZONAL_RESOLUTION)

	transition_table = {"start": { # initial "pseudostate" to decide who serves first
		(True, 0, 0, END_SCORE): 0.5,
		(False, 0, 0, END_SCORE): 0.5
	}}
	
	# transistion_table[i][j] will equal the probability of moving from state
	# i to state j in 1 step
	
	transient_states = ["start"]
	absorbing_states = []

	def calculate_probability(r, a_serves, a_score, b_score, playing_to):
		state = (a_serves, a_score, b_score, playing_to)
		transition_table[state] = {}
	
		if max(a_score, b_score) == playing_to:# or a_score == b_score == 4:
			absorbing_states.append(state)
			transition_table[state][state] = 1.0
			return
	
		if a_score == b_score == playing_to-1 == END_SCORE-1: # tie breaker
			if a_serves: # b chooses
				if (1-r)**2 - 3*(1-r) + 1 < 0:
					playing_to = END_SCORE+1
			else: # a chooses
				if r**2 - 3*r + 1 < 0:
					playing_to = END_SCORE+1
	
		transient_states.append(state)
	
		if a_serves:
			transition_table[state][(True, a_score+1, b_score, playing_to)] = r
			transition_table[state][(False, a_score, b_score, playing_to)] = 1-r
		else: # b serves
			transition_table[state][(True, a_score, b_score, playing_to)] = r
			transition_table[state][(False, a_score, b_score+1, playing_to)] = 1-r
	
		for new_state in transition_table[state].keys():
			# only if we haven't already checked:
			if new_state not in transient_states+absorbing_states:
				calculate_probability(r, *new_state)
	
	# recusrively construct transition table
	calculate_probability(r, True, 0, 0, END_SCORE);

	# convert transition table into numpy matrix, so we can do calculations with it
	# more easily

	P_array = []
	transients = len(transient_states)

	for i in transient_states+absorbing_states:
		probabilities = []
		for j in transient_states+absorbing_states:
			probabilities.append(transition_table[i].get(j, 0))
		P_array.append(probabilities)

	P = np.matrix(P_array)
	Q = P[0:transients, 0:transients]

	prev = 0
	cumulativeP = P*1 # make a copy of P
	
	for game_length in range(TOTAL_DURATION):
		cumulative = np.sum(cumulativeP[0,transients:])
		cumulativeP *= P
		H[game_length, n] = cumulative-prev
		prev = cumulative

	I = np.identity(transients)
	N = np.linalg.inv(I-Q)

	expectations[n] =  np.sum(N[0]) - 1 # subtract 1 to ignore starting state
	print("Progress: {}%".format(rarb*100)) # this program runs quite slowly...

X, Y = np.meshgrid(xedges, yedges)
plt.figure(figsize=(16,10))
plt.pcolormesh(X, Y, H, cmap="cubehelix_r",
	norm=colors.LogNorm(0.001, 1.0)).set_rasterized(True)
plt.axis([X.min(),X.max(),Y.min(),Y.max()])
plt.plot(xedges, expectations, lw=5, c="red")
plt.colorbar(label="Probability")
plt.grid()
plt.title("English Scoring")
plt.xlabel("ra/rb")
plt.ylabel("Game Length (rallies)")
plt.savefig("English.svg", dpi=300)
