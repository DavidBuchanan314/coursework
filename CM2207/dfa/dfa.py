from copy import deepcopy
import operator as op
from itertools import product
from queue import Queue


class DFA:
	def __init__(self, Q, Σ, δ, q0, F):
		assert(type(Q)  is set)
		assert(type(Σ)  is set)
		assert(type(δ)  is dict)  # a dict of dicts, of strings
		assert(type(q0) is str)
		assert(type(F)  is set)
		self.Q  = Q   # set of states
		self.Σ  = Σ   # set of symbols (Alphabet)
		self.δ  = δ   # transition function
		self.q0 = q0  # initial state
		self.F  = F   # set of accept states

	def complement(self):
		return ~self

	def intersection(self, Mʹ):
		return self & Mʹ

	def __and__(self, Mʹ):  # intersection
		return self.combine(Mʹ, op.and_)

	def union(self, Mʹ):
		return self | Mʹ

	def __or__(self, Mʹ):  # union
		return self.combine(Mʹ, op.or_)

	def symmetric_difference(self, Mʹ):
		return self ^ Mʹ

	def __xor__(self, Mʹ):  # symmetric_difference
		return self.combine(Mʹ, op.xor)

	def accepts_string(self, string):
		return string in self

	def get_string(self):
		"""
		Returns a string in the language described by the DFA, or None if the
		language is empty.

		The "search state" is described by the tuple:
			(current_state, current_string)

		There is no need to visit any state more than once (preventing infinite
		search loops from occuring).
		"""
		queue = Queue()
		queue.put((self.q0, ""))
		visited_states = set()

		while not queue.empty():
			current_state, current_string = queue.get()
			if current_state in visited_states:  # This state is already checked
				continue
			if current_state in self.F:  # we've reached an accept state
				return current_string
			visited_states.add(current_state)  # don't check this state again
			for next_symbol, next_state in self.δ[current_state].items():
				queue.put((next_state, current_string + next_symbol))

		return None

	def combine(self, Mʹ, comparison_op):
		"""
		Used to combine two DFAs, either to generate the intersection, union,
		or symmetric difference depending on the comparison_op argument given
		"""
		M = self
		Q_star  = set()
		δ_star  = dict()
		q0_star = state_str(M.q0, Mʹ.q0)
		F_star  = set()

		assert(M.Σ == Mʹ.Σ)

		for r, s in product(M.Q, Mʹ.Q):
			state = state_str(r, s)
			δ_star[state] = {
				c: state_str(M.δ[r][c], Mʹ.δ[s][c])
				for c in M.Σ
			}
			Q_star.add(state)
			if comparison_op((r in M.F), (s in Mʹ.F)):
				F_star.add(state)

		return DFA(Q_star, M.Σ, δ_star, q0_star, F_star)

	def __invert__(self):
		"""
		Underlying implementation of complement().
		Returns a new DFA instance that recognises the complement of the language
		recognised by itself.
		"""
		M̅ = deepcopy(self)
		M̅.F = M̅.Q - M̅.F
		return M̅

	def __eq__(self, Mʹ):
		"""
		The two languages will be equivalent if the symmetric difference is an empty
		language.
		"""
		return (self ^ Mʹ).get_string() is None

	def __str__(self):
		"""
		Dump the DFA info to a string in the required format.
		Note: Q, Σ and F are sorted into a consistent order such that the output of
		this function is deterministic.
		"""

		Q_list = sorted(list(self.Q))
		Σ_list = sorted(list(self.Σ))

		output_lines = []

		output_lines.append(str(len(Q_list)))
		output_lines.append(" ".join(Q_list))

		output_lines.append(str(len(Σ_list)))
		output_lines.append(" ".join(Σ_list))

		for Qi in Q_list:
			next_states = [self.δ[Qi][Σi] for Σi in Σ_list]
			output_lines.append(" ".join(next_states))

		output_lines.append(self.q0)

		output_lines.append(str(len(self.F)))
		output_lines.append(" ".join(sorted(list(self.F))))

		return "\n".join(output_lines)

	def __contains__(self, string):
		"""
		Underlying implementation of accepts_string().
		(Not a required feature, used for my personal testing).
		Checks whether a given string is accepted by the DFA
		"""

		assert(type(string) is str)

		q = self.q0

		for symbol in string:
			if symbol not in self.Σ:
				return False
			q = self.δ[q][symbol]

		return q in self.F


def load(fp):
	"""
	Accepts a File object as argument, and returns a new DFA object
	"""
	num_states = int(fp.readline())
	Q = fp.readline().strip().split()
	assert(len(Q) == num_states)

	num_symbols = int(fp.readline())
	Σ = fp.readline().strip().split()
	assert(len(Σ) == num_symbols)

	δ = dict()  # δ[current_state][next_symbol] -> next_state

	for current_state in Q:
		next_states = fp.readline().strip().split()
		assert(len(next_states) == num_symbols)
		δ[current_state] = dict(zip(Σ, next_states))

	"""
	Q and Σ needed to be ordered to enable the transition matrix to be parsed.
	From now on, it makes more sense to represent them as sets
	"""
	Q = set(Q)
	Σ = set(Σ)

	q0 = fp.readline().strip()

	num_accept_states = int(fp.readline())
	F = set(fp.readline().strip().split())
	assert(len(F) == num_accept_states)
	assert(F <= Q)  # F is a subset of Q

	return DFA(Q, Σ, δ, q0, F)


def save_jff(dfa, filename):  # quick and dirty
	jff = '<?xml version="1.0"?>\n<structure>\n\t<type>fa</type>\n'
	state_template = '\t<state name="{}" id="{}">{}</state>\n'
	transition_template = \
		"\t<transition>\n\t\t<from>{}</from>\n\t\t<to>{}</to>\n\t\t<read>{}</read>\n\t</transition>\n"

	states = list(dfa.Q)

	for state, _id in zip(states, range(len(states))):
		types = ""
		if state in dfa.F:
			types += "<final/>"
		if state == dfa.q0:
			types += "<initial/>"
		jff += state_template.format(state, _id, types)

	for src, read in product(dfa.Q, dfa.Σ):
		jff += transition_template.format(
			states.index(src),
			states.index(dfa.δ[src][read]),
			read
		)

	jff += "</structure>\n"

	with open(filename, "w") as outfile:
		outfile.write(jff)


def state_str(a, b):  # convenience function
	return "({},{})".format(a, b)
